<?php

namespace Systha\Core\Http\Resources;

class QuotationListResource extends QuotationResource
{
    public function toArray($request): array
    {
        return $this->listResponse();
    }
}
