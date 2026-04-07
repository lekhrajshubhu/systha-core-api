<?php

namespace Systha\Core\ServiceContainer;



use App\Mail\ClientWelcomeMail;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Systha\Core\Models\ClientModel;

class ClientOnboardingService
{
    public function sendWelcomeEmail(ClientModel $client, string $plainPassword): void
    {
        if (!$client->email) {
            return;
        }

        // Mail::to($client->email)->send(
        //     new ClientWelcomeMail($client, $plainPassword)
        // );
    }
}