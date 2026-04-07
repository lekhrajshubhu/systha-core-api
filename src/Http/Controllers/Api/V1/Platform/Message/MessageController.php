<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Message;

use Illuminate\Http\Request;
use Systha\Core\Models\Quote;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PDO;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Http\Resources\GlobalClientConversationResource;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\ClientModel;

/**
 * @group Platform
 * @subgroup Messages
 */
class MessageController extends Controller
{

    public function conversations(Request $request)
    {
        $client = auth('platform')->user();

        try {
            $conversations = ChatConversation::where('client_id', $client->id)
                ->with(['lastMessage', 'conversationable'])
                ->leftJoin('chat_messages', function ($join) {
                    $join->on('chat_messages.conversation_id', '=', 'chat_conversations.id')
                        ->whereRaw('chat_messages.id = (SELECT MAX(id) FROM chat_messages WHERE chat_messages.conversation_id = chat_conversations.id)');
                })
                ->orderByDesc('chat_messages.created_at')
                ->select('chat_conversations.*')
                ->get();

            $filter = [];
            foreach ($conversations as $conversation) {
                if ($conversation->type == 'other') {
                    if ($conversation->conversationable && in_array($conversation->conversationable->status, ["new", "pending", "booked"])) {
                        $filter[] = $conversation;
                    }
                } else {
                    $filter[] = $conversation;
                }
            }

            $result = collect($filter)->map(function ($conv) {
                $lastMessage = $conv->lastMessage;
                $title = 'Unknown';
                $initials = 'UN';

                if ($lastMessage) {
                    if ($lastMessage->table_from === 'vendors') {
                        $vendor = Vendor::find($lastMessage->table_from_id);
                        $title = $vendor ? $vendor->name : 'Unknown';
                        // Vendor initials logic
                        if ($vendor && $vendor->name) {
                            $parts = explode(' ', trim($vendor->name));
                            if (count($parts) === 1) {
                                $initials = strtoupper(mb_substr($parts[0], 0, 2));
                            } else {
                                $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
                            }
                        }
                    } elseif ($lastMessage->table_from === 'clients') {
                        $client = ClientModel::find($lastMessage->table_from_id);
                        $title = $client ? $client->fname : 'Unknown';
                        // Client initials logic
                        if ($client && $client->fname) {
                            $parts = explode(' ', trim($client->fname));
                            if (count($parts) === 1) {
                                $initials = strtoupper(mb_substr($parts[0], 0, 2));
                            } else {
                                $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
                            }
                        }
                    } else {
                        // Fallback for other types
                        $initials = collect(explode(' ', $title))
                            ->map(fn($w) => mb_substr($w, 0, 1))
                            ->join('');
                        $initials = strtoupper(mb_substr($initials, 0, 2));
                    }
                }

                $preview = $lastMessage->message ?? '';
                $preview = html_entity_decode(strip_tags($preview));
                $time = optional($lastMessage)->created_at
                    ? $lastMessage->created_at->diffForHumans()
                    : '';
                $avatarBg = '#dbeafe';
                $unreadCount = $conv->messages()->where('seen_client', 0)->count();
                $isOnline = false;

                return [
                    'id' => (string)$conv->id,
                    'title' => $title,
                    'preview' => $preview,
                    'time' => $time,
                    'initials' => $initials, // Always 2 characters
                    'avatarBg' => $avatarBg,
                    'unreadCount' => $unreadCount,
                    'isOnline' => $isOnline,
                ];
            })->values();

            return response(['data' => $result], 200);

        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $client = auth('platform')->user();
            $chatConv = ChatConversation::with('messages', 'conversationable')->find($id);
            if (!$chatConv) {
                return response(["error" => "Conversation not found."], 404);
            }
            $contact = $client ? $client->contact : null;
            $this->handleSeen($contact, $chatConv);
            return response(["data" => $chatConv, "contact" => $contact], 200);
        } catch (\Throwable $th) {
            // Error handling
            return response(["error" => $th->getMessage() . " line :" . $th->getFile()], 422);
        }
    }
    public function handleSeen($contact, $chatConv)
    {
        if (!$contact) {
            return;
        }

        if ($contact->table_name === 'clients') {
            $chatConv->messages()
                ->where('seen_client', 0) // Update only unseen messages
                ->update(['seen_client' => 1]);
        } elseif ($contact->table_name === 'vendors') {
            $chatConv->messages()
                ->where('seen_vendor', 0) // Update only unseen messages
                ->update(['seen_vendor' => 1]);
        } elseif ($contact->table_name === 'service_providers') {
            $chatConv->messages()
                ->where('seen_provider', 0) // Update only unseen messages
                ->update(['seen_provider' => 1]);
        }
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            "message" => "required|string"
        ]);

        $client = auth('platform')->user();
        if (!$client) {
            return response(["error" => "Unauthenticated."], 401);
        }

        $conv = ChatConversation::with('members')->find($conversationId);
        if (!$conv) {
            return response(["error" => "Conversation not found."], 404);
        }

        $senderTable = 'clients';
        $senderId = $client->id;

        if ($client->contact && isset($client->contact->table_name, $client->contact->table_id)) {
            $senderTable = $client->contact->table_name;
            $senderId = $client->contact->table_id;
        }

        $senderMember = $conv->members->first(function ($member) use ($senderTable, $senderId) {
            return $member->table_name === $senderTable && (int) $member->table_id === (int) $senderId;
        });

        if (!$senderMember) {
            return response(["error" => "Sender is not part of this conversation."], 422);
        }

        $receiverMember = $conv->members->first(function ($member) use ($senderMember) {
            return !($member->table_name === $senderMember->table_name && (int) $member->table_id === (int) $senderMember->table_id);
        });

        if (!$receiverMember) {
            return response(["error" => "Receiver could not be resolved for this conversation."], 422);
        }

        $message = $conv->messages()->create([
            "message" => $request->message,
            "table_from_id" => $senderMember->table_id,
            "table_from" => $senderMember->table_name,
            "table_to_id" => $receiverMember->table_id,
            "table_to" => $receiverMember->table_name,
        ]);

        return response([
            "data" => $message,
            "message" => "Message sent successfully",
        ], 201);

    }
}
