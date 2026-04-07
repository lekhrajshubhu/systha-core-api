<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaticPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->page_name,
            'page' => $this->page_code,
            'content' => $this->page_content,
        ];
    }
}
