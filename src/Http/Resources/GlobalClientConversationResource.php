<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GlobalClientConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'is_active' => $this->is_active,
            'last_message' => $this->whenLoaded('lastMessage', function () {
                return [
                    'id'             => $this->lastMessage->id ?? null,
                    'message'        => $this->lastMessage->message ?? null,
                    'seen_client'    => $this->lastMessage->seen_client ?? false,
                    'seen_vendor'    => $this->lastMessage->seen_vendor ?? false,
                    'seen_provider'  => $this->lastMessage->seen_provider ?? false,
                ];
            }),
            'message_to' => [
                'id' => $this->messageTo->id,
                'name' => $this->messageTo->contact->table_name == 'vendors' ? $this->messageTo->name : $this->messageTo->fullName,
                'icon' => $this->messageTo->logo ? $this->messageTo->logo : $this->messageTo->avatar,
            ],
            'message_from' => [
                'id' => $this->messageFrom->id,
                'icon' => $this->messageFrom->logo ? $this->messageFrom->logo : $this->messageFrom->avatar,
            ],
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}
