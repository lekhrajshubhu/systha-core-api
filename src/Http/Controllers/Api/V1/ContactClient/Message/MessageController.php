<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Message;

use Illuminate\Http\Request;
use Systha\Core\Models\Quote;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PDO;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Http\Resources\ConversationResource;

/**
 * @group Contacts
 * @subgroup Messages
 */
class MessageController extends Controller
{

    public function conversations(Request $request)
    {
        $contact = auth('contacts')->user();
        $clientId = $contact->table_id;

        try {
            //code...
            $conversations = ChatConversation::where('client_id', $clientId)
                ->with(['lastMessage', 'conversationable'])
                ->leftJoin('chat_messages', function ($join) {
                    $join->on('chat_messages.conversation_id', '=', 'chat_conversations.id')
                        ->whereRaw('chat_messages.id = (SELECT MAX(id) FROM chat_messages WHERE chat_messages.conversation_id = chat_conversations.id)');
                })
                ->orderByDesc('chat_messages.created_at')
                ->select('chat_conversations.*') // Prevent conflicting columns
                ->get();

            $filter = [];
            foreach ($conversations as $conversation) {
                if ($conversation->type == 'other') {
                    if (in_array($conversation->conversationable->status, ["new", "pending", "booked"])) {
                        $filter[] = $conversation;
                    }
                } else {
                    $filter[] = $conversation;
                }
            }

            return  ConversationResource::collection($filter);
            // return response(['data' => $filter], 200);
        } catch (\Throwable $th) {
            return response(['error' => $th->getFile()], 422);
            //throw $th;
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $contact = auth('contacts')->user();
            $chatConv = ChatConversation::with('messages', 'conversationable')->find($id);
            if (!$chatConv) {
                return response(["error" => "Conversation not found."], 404);
            }
            $this->handleSeen($contact, $chatConv);
            return response(["data" => $chatConv, "contact" => $contact], 200);
        } catch (\Throwable $th) {
            // Error handling
            return response(["error" => $th->getMessage() . " line :" . $th->getFile()], 422);
        }
    }
    public function handleSeen($contact, $chatConv)
    {
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

    public function sendMessage(Request $request, $conversationId){
        
        $request->validate([
            "message" =>"required|string"
        ]);

        $conv = ChatConversation::find($conversationId);

        $message = $conv->messages()->create([
            "message" =>$request->message,
            "table_from_id" =>$conv->messageFrom->table_id,
            "table_from" =>$conv->messageFrom->table_name,
            "table_to_id" =>$conv->messageTo->table_id,
            "table_to" =>$conv->messageTo->table_name,
        ]);

        return response([
            "data" => $message,
            "message"=>"Message sent successfully",
        ],201);

    }
}
