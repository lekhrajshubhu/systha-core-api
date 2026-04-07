<?php

namespace Systha\Core\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Systha\Core\Models\Contact;
use Systha\Core\Http\Controllers\BaseController;


class PasswordResetController extends BaseController
{
    // Handle password reset by email
    public function resetPasswordByEmail(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email|exists:clients,email', // Assuming Contact is your model for users
        ]);

        // Find the user by email
        $user = Contact::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // dd($user);
        // Generate a new random password
        $newPassword = Str::random(8); // You can increase the length if you prefer

        // Update the user's password
        $user->password = bcrypt($newPassword);

        // dd($newPassword);
        $user->save();

        // Send the new password to the user's email
        Mail::send('core::mail.password_reset', [
            'password' => $newPassword,
            'user' => $user,
            'vendor' => $this->vendor,
            'logo' => "noimage.png",
        ], function ($message) use ($user) {
            $message->from($this->vendor->contact->email,$this->vendor->name)
                    ->to($user->email)
                    ->subject('Your Password Has Been Reset');
        });
        

        return response()->json(['message' => 'A new password has been sent to your email.']);
    }
}
