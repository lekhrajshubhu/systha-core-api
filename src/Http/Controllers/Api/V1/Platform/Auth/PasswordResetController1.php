<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Systha\Core\Models\ClientModel;
use Systha\Core\Services\CustomMailService;
use Systha\Core\Services\DefaultMailService;

class PasswordResetController1 extends Controller
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

        $emailData = [
            'from_email' => 'system@systha.com',
            'from_name' => 'Support',
            'to_email' => $client->email,
            'to_name' => $client->fname ?? 'User',
            'subject' => 'Your Password Has Been Reset',
            'message' => view('core::mail.password_reset', [
                'password' => $newPassword,
                'user' => $client,
                'logo' => 'noimage.png',
            ])->render(),
            'table_name' => 'clients',
            'table_id' => $client->id,
        ];

        try {
            $mailService = app(DefaultMailService::class);
            $result = $mailService->send($emailData);

            if (empty($result['success']) || !$result['success']) {
                return response()->json([
                    'message' => 'Failed to send email.',
                    'error' => $result['error'] ?? $result['message'] ?? 'Unknown error',
                ], 500);
            }
            $mailer = Mail::build([
                'transport' => 'smtp',
                'host' => 'sandbox.smtp.mailtrap.io',
                'port' => 2525,
                'username' => '0811af19328a57',
                'password' => '037e6cf790b336',
                'encryption' => 'tls',
            ]);

            $mailable = new class($emailData) extends Mailable {
                public $emailData;
                public function __construct($emailData)
                {
                    $this->emailData = $emailData;
                }
                public function build()
                {
                    return $this->from($this->emailData['from_email'], $this->emailData['from_name'])
                        ->subject($this->emailData['subject'])
                        ->html($this->emailData['message']);
                }
            };

            Mail::to($client->email)->send($mailable);


            return response()->json(['message' => 'A new password has been sent to your email.']);
        } catch (\Throwable $th) {
            header('Access-Control-Allow-Origin: *');
            dd($th)
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
