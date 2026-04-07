<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Systha\Core\Models\ClientModel;
use Systha\Core\Services\CustomMailService;
use Systha\Core\Services\EmailLogoService;

class PasswordResetController extends Controller
{
    /**
     * @subgroup Auth
     */
    public function resetPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $client = ClientModel::where('email', $request->email)->first();

        if (!$client) {
            return response()->json(['message' => 'Email does not exist.'], 404);
        }

        $newPassword = \Illuminate\Support\Str::random(8);
        $client->password = bcrypt($newPassword);
        $client->save();

        $logoUrl = app(EmailLogoService::class)->companyLogoDataUri($request->attributes->get('company'));


        $emailData = [
            'from_email' => 'info@systha.com',
            'from_name' => 'Systha Support',
            'to_email' => $client->email,
            'to_name' => $client->fname ?? 'User',
            'subject' => 'Your Password Has Been Reset',
            'message' => view('core::mail.password_reset', [
                'password' => $newPassword,
                'user' => $client,
                'logo' => $logoUrl,
            ])->render(),
            'table_name' => 'clients',
            'table_id' => $client->id,
        ];

        try {
            $mailService = app(CustomMailService::class);
            $result = $mailService->send($emailData);

            if (empty($result['success']) || !$result['success']) {
                return response()->json([
                    'message' => 'Failed to send email.',
                    'error' => $result['error'] ?? $result['message'] ?? 'Unknown error',
                ], 500);
            }

            return response()->json(['message' => 'A new password has been sent to your email.']);
        } catch (\Throwable $th) {
            Log::error('Password reset email failed', [
                'error' => $th->getMessage(),
                'email' => $client->email,
            ]);
            return response([
                'message' => 'Failed to send email.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

}
