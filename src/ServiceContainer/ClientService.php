<?php

namespace Systha\Core\ServiceContainer;



use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Systha\Core\DTO\ClientDto;
use Systha\Core\Models\ClientModel;

class ClientService
{
    public function create(ClientDto $dto): array
    {
        $plainPassword = $dto->password ?: Str::password(10);

        $client = ClientModel::create([
            ...$dto->toArray(),
            'password' => Hash::make($plainPassword),
        ]);

        return [
            'client' => $client,
            'plain_password' => $plainPassword,
        ];
    }

    public function update(ClientModel $client, ClientDto $dto): ClientModel
    {
        $client->update($dto->toArray());

        return $client->refresh();
    }

    public function createOrUpdate(ClientDto $dto): array
    {
        $client = ClientModel::where('email', $dto->email)->first();

        if ($client) {
            return [
                'client' => $this->update($client, $dto),
                'plain_password' => null,
                'was_created' => false,
            ];
        }

        $created = $this->create($dto);

        return [
            ...$created,
            'was_created' => true,
        ];
    }
}