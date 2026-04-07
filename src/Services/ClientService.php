<?php

namespace Systha\Core\Services;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Client;
use Systha\Core\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Systha\Core\Models\VendorClient;


class ClientService
{
    public function createClient(array $data, $vendor = null): array
    {
        // Validate data
        $validator = Validator::make($data, [
            'contact.fname' => 'required|string|max:255',
            'contact.lname' => 'required|string|max:255',
            'contact.email' => 'required|email|max:255',
            'contact.phone_no' => 'nullable|string|max:20',
            'contact.password' => 'nullable|string',

            'address.add1' => 'required_with:address|string|max:255',
            'address.city' => 'required_with:address|string|max:100',
            'address.state' => 'required_with:address|string|max:100',
            'address.zip' => 'required_with:address|string|max:20',
            'address.country' => 'nullable|string|max:100',
            'address.lat' => 'nullable|numeric',
            'address.lon' => 'nullable|numeric',
            'address.lng' => 'nullable|numeric',
            'address.address_type' => 'nullable|in:primary,other',
            'is_global' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $validator->validated();
        $contactData = $validated['contact'];
        $addressData = $validated['address'] ?? [];
        $passwordPlain = isset($contactData['password']) ? base64_decode($contactData['password']): null;


        // Check if contact exists
        $contact = Contact::where('email', $contactData['email'])
            ->where('contact_type', 'customer')
            ->first();

        if ($contact && $contact->user()) {
            $client = $contact->user();
        } else {
            // Create client
            $client = Client::create([
                'fname' => $contactData['fname'],
                'lname' => $contactData['lname'],
                'email' => $contactData['email'],
                'phone_no' => $contactData['phone_no'] ?? null,
                'vendor_id' => $vendor?->vendor_id,
                'state' => 'publish',
                'password' => isset($data['is_global']) ==1 ? null : bcrypt($passwordPlain),
            ]);

            // Create contact
            $client->contact()->create([
                'fname' => $contactData['fname'],
                'lname' => $contactData['lname'],
                'email' => $contactData['email'],
                'phone_no' => $contactData['phone_no'] ?? null,
                'contact_type' => 'customer',
            ]);
        }


        if ($vendor && $client) {
            $linkExists = DB::table('vendor_client')
                ->where('vendor_id', $vendor->id)
                ->where('client_id', $client->id)
                ->exists();

            if (! $linkExists) {
                if (! $passwordPlain) {
                    $passwordPlain = Str::random(10);
                }

                VendorClient::create([
                    'vendor_id' => $vendor->id,
                    'vendor_code' => $vendor?->vendor_code,
                    'client_id' => $client->id,
                    'email' => $contactData['email'],
                    'client_type' => 'customer',
                    'password' => isset($data['is_global']) ? bcrypt($passwordPlain) : null,
                ]);
            }
        }

        // Handle address if available
        $address = null;
        if (!empty($addressData)) {
            $address = $this->createAddress($client, $addressData);
        }

        return [
            'client' => $client,
            'address' => $address,
            'password' => $passwordPlain,
        ];
    }

    public function createAddress(Client $client, array $addressData)
    {
        $type = $addressData['address_type'] ?? 'primary';

        // Check if address of this type already exists
        $existing = $client->address()->where('address_type', 'primary')->first();

        $isDefault = 1;
        if ($existing) {
            $type = "other";
            $isDefault = 0;
        }

        // Prepare payload
        $payload = [
            'add1' => $addressData['add1'],
            'add2' => $addressData['add2'] ?? '',
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'zip' => $addressData['zip'],
            'country' => $addressData['country'] ?? '',
            'lat' => $addressData['lat'] ?? '',
            'lon' => $addressData['lon'] ?? ($addressData['lng'] ?? ''),
            'is_default' => $isDefault,
            'address_type' => $type,
        ];

        return $client->address()->create($payload);
    }
}
