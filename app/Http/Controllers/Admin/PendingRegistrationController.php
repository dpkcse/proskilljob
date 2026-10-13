<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use App\Notifications\EmailVerifyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PendingRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $pendingUsers = PendingUser::query()
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('expires_at', '>', now());
                } elseif ($request->status === 'expired') {
                    $query->where('expires_at', '<=', now());
                } elseif ($request->status === 'mail_failed') {
                    $query->whereNotNull('verification_last_error');
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('backend.pending-registrations.index', compact('pendingUsers'));
    }

    public function resend(PendingUser $pendingUser)
    {
        if ($pendingUser->last_verification_sent_at?->isAfter(now()->subMinute())) {
            return back()->with('error', 'Please wait at least one minute before sending another verification email.');
        }

        $token = Str::random(60);
        $pendingUser->update([
            'verification_token' => $token,
            'expires_at' => now()->addHours(24),
            'verification_send_attempts' => $pendingUser->verification_send_attempts + 1,
            'verification_last_error' => null,
        ]);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $pendingUser->email],
            ['token' => $token, 'created_at' => now()]
        );

        try {
            Notification::route('mail', $pendingUser->email)
                ->notify(new EmailVerifyNotification($pendingUser->email, $token));

            $pendingUser->update(['last_verification_sent_at' => now()]);

            return back()->with('success', 'Verification email sent successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            $pendingUser->update([
                'verification_last_error' => Str::limit($exception->getMessage(), 1000),
            ]);

            return back()->with('error', 'Verification email could not be sent. Check the mail provider and try again.');
        }
    }

    public function destroy(PendingUser $pendingUser)
    {
        DB::transaction(function () use ($pendingUser) {
            DB::table('password_resets')->where('email', $pendingUser->email)->delete();
            $pendingUser->delete();
        });

        return back()->with('success', 'Pending registration deleted.');
    }
}
