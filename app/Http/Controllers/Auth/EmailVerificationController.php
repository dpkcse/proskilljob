<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class EmailVerificationController extends Controller
{
    /**
     * Verify email with token and create user account
     */
    public function verifyEmail(Request $request, $token)
    {
        // Find the pending user by verification token
        $pendingUser = PendingUser::where('verification_token', $token)
            ->valid() // Only get non-expired records
            ->first();

        if (! $pendingUser) {
            abort(401, 'Invalid or expired verification token.');
        }

        // Create the user account
        try {
            DB::beginTransaction();

            $user = User::create([
                'role' => $pendingUser->role,
                'name' => $pendingUser->name,
                'username' => $pendingUser->username,
                'email' => $pendingUser->email,
                'password' => $pendingUser->password, // Already hashed in PendingUser
                'created_ip' => $pendingUser->created_ip,
                'email_verified_at' => now(),
            ]);

            // Delete the pending user record and verification token
            $pendingUser->delete();

            // Also clean up the password_resets table
            DB::table('password_resets')->where('email', $user->email)->delete();

            DB::commit();

            // Login the new user
            Auth::login($user);

            // Redirect based on role
            if ($user->role === 'company') {
                return redirect()->route('company.dashboard', ['verified' => true])
                    ->with('success', 'Email verified successfully! Welcome to your dashboard.');
            } else {
                return redirect()->route('candidate.dashboard', ['verified' => true])
                    ->with('success', 'Email verified successfully! Welcome to your dashboard.');
            }

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('register')
                ->with('error', 'Something went wrong during verification. Please try registering again.');
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:pending_users,email',
        ]);

        $pendingUser = PendingUser::where('email', $request->email)
            ->valid()
            ->first();

        if (! $pendingUser) {
            return redirect()->back()
                ->with('error', 'No pending registration found for this email or token has expired.');
        }

        // Resend verification email (reuse existing notification logic)
        if (checkMailConfig() && setting('email_verification')) {
            $token = DB::table('password_resets')->where('email', $pendingUser->email)->first();

            if ($token) {
                Notification::route('mail', $pendingUser->email)
                    ->notify(new \App\Notifications\EmailVerifyNotification($pendingUser->email, $token->token));

                return redirect()->back()
                    ->with('success', 'Verification email has been resent to your email address.');
            }
        }

        return redirect()->back()
            ->with('error', 'Unable to resend verification email. Please contact support.');
    }
}
