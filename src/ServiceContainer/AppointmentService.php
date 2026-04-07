<?php

namespace Systha\Core\ServiceContainer;

use Systha\Core\Models\ClientModel;
use Systha\Core\Models\AppointmentModel;

class AppointmentService
{
    public function createForClient(ClientModel $client, array $data): AppointmentModel
    {
        return $client->appointments()->create([
            'subscription_id' => $data['subscription_id'] ?? null,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'stripe_subscription_id' => $data['stripe_subscription_id'] ?? null,
        ]);
    }
}