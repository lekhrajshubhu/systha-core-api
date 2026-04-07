<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Message;

use Illuminate\Http\Request;
use Systha\Core\Models\Quote;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PDO;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Http\Resources\MessageResource;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\ClientModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Systha\Core\Models\ServiceProvider;

/**
 * @group Platform
 * @subgroup Messages
 */
class MessageController extends Controller
{

    public function messageList(Request $request)
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

            $search = trim($request->input('search', ''));
            if ($search !== '') {
                $searchLower = Str::lower($search);
                $filter = collect($filter)->filter(function ($conv) use ($searchLower) {
                    $fields = [];

                    // computed title used in API response
                    $fields[] = $this->conversationTitle($conv);

                    // conversation title
                    $fields[] = $conv->title ?? '';

                    // last message text
                    $fields[] = optional($conv->lastMessage)->message ?? '';

                    // conversationable name variants
                    if ($conv->conversationable) {
                        $fields[] = $conv->conversationable->name ?? '';
                        $fields[] = $conv->conversationable->fullName ?? '';
                        $fields[] = $conv->conversationable->fname ?? '';
                    }

                    foreach ($fields as $value) {
                        if ($value !== null && $value !== '' && Str::contains(Str::lower($value), $searchLower)) {
                            return true;
                        }
                    }

                    return false;
                })->values()->all();
            }

            $perPage = (int) ($request->input('per_page') ?? 10);
            $page = LengthAwarePaginator::resolveCurrentPage();
            $filtered = collect($filter);

            $paginated = new LengthAwarePaginator(
                $filtered->forPage($page, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            $unreadTotal = collect($filter)->sum(function ($conv) {
                return $conv->messages()->where('seen_client', 0)->count();
            });

            $totalMessages = collect($filter)->sum(function ($conv) {
                return $conv->messages()->count();
            });

            return response()->json([
                'data' => MessageResource::collection($paginated->items()),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'from' => $paginated->firstItem(),
                    'last_page' => $paginated->lastPage(),
                    'path' => $paginated->path(),
                    'per_page' => $paginated->perPage(),
                    'to' => $paginated->lastItem(),
                    'total' => $paginated->total(),
                ],
                'message_count' => [
                    'unread' => $unreadTotal,
                    'total' => $totalMessages,
                ],
            ]);
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

            $this->handleSeen($client, $chatConv);

            $messages = $chatConv->messages()
                ->orderBy('created_at')
                ->get()
                ->map(function ($message) use ($client) {
                    $senderName = $this->nameFromTable($message->table_from, $message->table_from_id);
                    return [
                        'id' => 'msg-' . $message->id,
                        'role' => $this->isOutgoing($client, $message) ? 'outgoing' : 'incoming',
                        'initials' => $this->initialsFromName($senderName),
                        'text' => $message->message,
                        'time' => optional($message->created_at)->format('g:i A'),
                    ];
                });

            $vendorInfo = null;
            $vendorMember = $chatConv->members()->where('table_name', 'vendors')->first();
            if ($vendorMember) {
                $vendorModel = Vendor::find($vendorMember->table_id);
                if ($vendorModel) {
                    $vendorInfo = [
                        'id' => $vendorModel->id,
                        'name' => $vendorModel->name,
                        'code' => $vendorModel->vendor_code,
                        'logo' => $vendorModel->logo ? asset($vendorModel->logo) : null,
                    ];
                }
            }

            return response([
                "data" => $messages,
                "client" => $client,
                "vendor" => $vendorInfo,
            ], 200);
        } catch (\Throwable $th) {
            // Error handling
            return response(["error" => $th->getMessage() . " line :" . $th->getFile()], 422);
        }
    }
    public function handleSeen($client, $chatConv)
    {
        if (!$client) {
            return;
        }

        $chatConv->messages()
            ->where('seen_client', 0) // Update only unseen messages
            ->update(['seen_client' => 1]);
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
            'seen_client' => 1,
        ]);

        return response([
            "data" => $message,
            "message" => "Message sent successfully",
        ], 201);
    }

    private function conversationTitle($conv): string
    {
        $title = 'Unknown';
        $lastMessage = $conv->lastMessage;

        if ($lastMessage) {
            if ($lastMessage->table_from === 'vendors') {
                $vendor = Vendor::find($lastMessage->table_from_id);
                $title = $vendor ? $vendor->name : $title;
            } elseif ($lastMessage->table_from === 'clients') {
                $client = ClientModel::find($lastMessage->table_from_id);
                $title = $client ? $client->fname : $title;
            }
        }

        return $title ?? 'Unknown';
    }

    private function isOutgoing($client, $message): bool
    {
        if (!$client) {
            return false;
        }

        return $message->table_from === $client->getTable()
            && (int) $message->table_from_id === (int) $client->id;
    }

    private function nameFromTable($table, $id): string
    {
        if ($table === 'vendors') {
            $vendor = Vendor::find($id);
            return $vendor?->name ?? 'Unknown';
        }

        if ($table === 'clients') {
            $client = ClientModel::find($id);
            return $client?->fname ?? 'Unknown';
        }

        if ($table === 'service_providers') {
            $sp = ServiceProvider::find($id);
            return $sp?->name ?? 'Unknown';
        }

        return 'Unknown';
    }

    private function initialsFromName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'UN';
        }

        $parts = explode(' ', $name);
        if (count($parts) === 1) {
            return strtoupper(mb_substr($parts[0], 0, 2));
        }

        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    }
}
