<?php

namespace Systha\Core\DTO;




use Illuminate\Http\Request;

class ClientDto
{
    public function __construct(
        public string $fname,
        public string $lname,
        public ?string $email,
        public ?string $email2,
        public ?string $phone1,
        public ?string $phone2,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            fname: $request->fname,
            lname: $request->lname,
            email: $request->email,
            email2: $request->email2 ?? null,
            phone1: $request->phone1 ?? null,
            phone2: $request->phone2 ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'fname'      => $this->fname,
            'lname'      => $this->lname,
            'email'      => $this->email,
            'email2'   => $this->email2,
            'phone1'   => $this->phone1,
            'phone2'   => $this->phone2,
        ];
    }
}