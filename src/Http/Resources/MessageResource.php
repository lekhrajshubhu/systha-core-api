<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\Vendor;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $lastMessage = $this->lastMessage;
        $title = 'Unknown';
        $initials = 'UN';

        if ($lastMessage) {
            if ($lastMessage->table_from === 'vendors') {
                $vendor = Vendor::find($lastMessage->table_from_id);
                $title = $vendor ? $vendor->name : 'Unknown';
                $initials = $this->initialsFromName($vendor?->name ?? '');
            } elseif ($lastMessage->table_from === 'clients') {
                $client = ClientModel::find($lastMessage->table_from_id);
                $title = $client ? $client->fname : 'Unknown';
                $initials = $this->initialsFromName($client?->fname ?? '');
            } else {
                $initials = $this->initialsFromName($title);
            }
        }

        $preview = $lastMessage->message ?? '';
        $preview = html_entity_decode(strip_tags($preview));
        $time = optional($lastMessage)->created_at
            ? $lastMessage->created_at->diffForHumans()
            : '';
        $avatarBg = '#dbeafe';
        $unreadCount = $this->messages()->where('seen_client', 0)->count();

        return [
            'id' => (string) $this->id,
            'title' => $title,
            'preview' => $preview,
            'time' => $time,
            'initials' => $initials,
            'avatarBg' => $avatarBg,
            'unreadCount' => $unreadCount,
            'isOnline' => false,
            'messages' => $this->whenLoaded('messages', function () {
                return $this->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'from_table' => $message->table_from,
                        'from_id' => $message->table_from_id,
                        'to_table' => $message->table_to,
                        'to_id' => $message->table_to_id,
                        'created_at' => optional($message->created_at)->toDateTimeString(),
                    ];
                });
            }),
        ];
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
