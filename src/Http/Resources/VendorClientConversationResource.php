<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorClientConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $messageTo = $this->messageTo;
        $messageFrom = $this->messageFrom;
        $messageToContact = $messageTo && isset($messageTo->contact) ? $messageTo->contact : null;

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
                'id' => $messageTo->id ?? null,
                'name' => ($messageToContact && ($messageToContact->table_name ?? null) === 'vendors')
                    ? ($messageTo->name ?? null)
                    : ($messageTo->fullName ?? $messageTo->name ?? null),
                'icon' => $messageTo->logo ?? $messageTo->avatar ?? null,
            ],
            'message_from' => [
                'id' => $messageFrom->id ?? null,
                'icon' => $messageFrom->logo ?? $messageFrom->avatar ?? null,
            ],
            'created_at'   => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'   => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
