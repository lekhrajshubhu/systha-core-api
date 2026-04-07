<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Systha\Core\DTO\ClientDto;
use Systha\Core\Models\Client;
use Systha\Core\Services\CustomMailService;
use Systha\Core\Services\EmailLogoService;

/**
 * @group Platform
 * @subgroup Auth
 */
class SignupController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:clients,email'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'min:8', 'same:password'],
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }


        $fullName = trim($request->get('full_name'));
        [$fname, $lname] = array_pad(explode(' ', $fullName, 2), 2, '');

        // hydrate fname/lname from full_name and forward the Request object to the DTO factory
        $request->merge([
            'fname' => $fname,
            'lname' => $lname,
        ]);

        $clientDto = ClientDto::fromRequest($request);

        try {
            $client = Client::create([
                ...$clientDto->toArray(),
                'password' => Hash::make($request->password),
            ]);

            $logoUrl = app(EmailLogoService::class)->companyLogoDataUri($request->attributes->get('company'));

            $emailData = [
                'from_email' => 'info@systha.com',
                'from_name' => 'Systha Support',
                'to_email' => $client->email,
                'to_name' => trim($client->fname . ' ' . $client->lname) ?: 'User',
                'subject' => 'Welcome to Systha',
                'message' => view('core::mail.signup', [
                    'user' => $client,
                    'logo' => $logoUrl,
                ])->render(),
                'table_name' => 'clients',
                'table_id' => $client->id,
            ];

            $mailService = app(CustomMailService::class);
            $result = $mailService->send($emailData);

            if (empty($result['success']) || !$result['success']) {
                return response()->json([
                    'message' => 'Signup successful, but failed to send welcome email.',
                    'client' => $client,
                    'error' => $result['error'] ?? $result['message'] ?? 'Unknown error',
                ], 201);
            }

            return response()->json([
                'message' => 'Signup successful',
                'client' => $client,
            ], 201);
        } catch (\Throwable $th) {
            Log::error('Signup failed', [
                'error' => $th->getMessage(),
                'email' => $request->email,
            ]);

            return response()->json([
                'message' => 'Signup failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

}
