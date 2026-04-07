<?php
namespace Systha\Core\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Systha\vendorpackage\Models\Vendor;
use Systha\vendorpackage\Models\ChatArchive;
use Systha\vendorpackage\Models\ChatMessage;
use Systha\vendorpackage\Models\ChatConversation;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

trait Chat
{
    abstract function chatUsersSelect();
    public function chatUsers()
    {
        $me = $this->getTable();
        $target = $this instanceof Vendor ? 'clients' : 'vendors';
        return $this->chatUsersSelect()
            ->selectSub(function($query) use ($target, $me) {
                $query->selectRaw('id')->from('chat_messages');
                $query->where(function($query) use ($target, $me) {
                    $query->orwhere(function($where) use ($target, $me) {
                        $where->where('chat_messages.table_from_id', $this->id);
                        $where->where('chat_messages.table_from', $me);
                        $where->where('chat_messages.table_to', $target);
                        $where->whereColumn('chat_messages.table_to_id', "$target.id");
                    });
                    $query->orWhere(function($where) use ($target, $me) {
                        $where->where('chat_messages.table_to_id', $this->id);
                        $where->where('chat_messages.table_to', $me);
                        $where->where('chat_messages.table_from', $target);
                        $where->whereColumn('chat_messages.table_from_id', "$target.id");
                    });
                });
                $query->orderByDesc('id')->limit(1);
            }, 'last_message')
            ->whereNull('ac.table_id')
            // ->where('id', $this->vendor_id)
            ->orderByDesc('last_message')
            ->havingRaw('last_message is not null')
            ->with('lastMessage')
            ->withCount('unreadMessages')
            ->get();
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_message');
    }

    public function unreadMessages() {
        try {
            $user = auth('contact')->setToken(request()->access_token)->user();
        } catch (TokenInvalidException $exception) {
            $user = null;
        }
        return $this->hasMany(ChatMessage::class, 'table_from_id')
                ->where('is_seen', 0)
                ->when($user, function($query, $contact) {
                    // dd($contact->table, $contact->table_id);
                    $query->where('table_to_id', $contact->table_id);
                })
                ->where('table_from', $this->getTable());
    }

    public function lastMessagesWith($chatUser, $page = 1)
    {
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $query1 = ChatMessage::query()
            ->where('table_from', $this->getTable())
            ->where('table_from_id', $this->getKey())
            ->where('table_to', $chatUser->getTable())
            ->where('table_to_id', $chatUser->getKey());

        $query2 = ChatMessage::query()
            ->where('table_to', $this->getTable())
            ->where('table_to_id', $this->getKey())
            ->where('table_from', $chatUser->getTable())
            ->where('table_from_id', $chatUser->getKey())
            ->union($query1)
            ->orderByDesc('created_at');

        $count = $query2->count();
        $more = ($page * $limit) < $count;
        return $query2->limit($limit)->offset($offset)->with('files')->get()->map(function ($item) use ($more) {
            $item->more = $more;
            return $item;
        });
    }

    public function archiveChatQuery()
    {
        return DB::raw(
            "(select table_name, table_id
                from chat_archives
                where from_table='{$this->getTable()}'
                and from_table_id={$this->getKey()}
                and is_deleted=0
            ) as ac"
        );
    }

    public function isArchivedBy(Model $sender)
    {
        return ChatArchive::query()->where([
            'from_table' => $sender->getTable(),
            'from_table_id' => $sender->getKey(),
            'table_id' => $this->getKey(),
            'table_name' => $this->getTable(),
            'is_deleted' => 0,
        ])->exists();
    }

    public function readMessagesWith(Model $chatUser)
    {
        ChatMessage::query()->where([
            'table_from' => $chatUser->getTable(),
            'table_from_id' => $chatUser->getKey(),
            'table_to' => $this->getTable(),
            'table_to_id' => $this->getKey(),
            'is_seen' => 0,
        ])->update([
            'is_seen' => 1,
        ]);
    }

    public function unarchive(Model $chatUser)
    {
        $archive = ChatArchive::query()->firstOrNew([
            'from_table' => $this->getTable(),
            'from_table_id' => $this->getKey(),
            'table_name' => $chatUser->getTable(),
            'table_id' => $chatUser->getKey(),
            'is_deleted' => 0,
        ]);
        if (!$archive->exists) {
            return;
        }

        save_update($archive, ['is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0]);
    }

    public function sendMessage(Model $target, string $text, $convId, $title=""): ChatMessage
    {
        $message = new ChatMessage();
        $message->title = $title;
        $message->table_from = $this->getTable();
        $message->table_from_id = $this->getKey();
        $message->table_to = $target->getTable();
        $message->table_to_id = $target->getKey();
        $message->conversation_id = $convId;
        $message->message = $text;
        $message->save();
        return $message;
    }

    public function conversations(){
        return ChatConversation::where('is_deleted',0)
        ->whereHas('members',function($query) {
            $query->where(['table_name' => $this->getTable(), "table_id"=>$this->id]);
        })
        ->with(['lastMessage','members.user'])
        ->get()
        ->sortByDesc('lastMessage.created_at')->values();
    }
}
