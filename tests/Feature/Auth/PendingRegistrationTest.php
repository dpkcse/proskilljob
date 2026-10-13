<?php

use App\Models\PendingUser;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

test('an expired pending email can register again', function () {
    config(['captcha.active' => false]);
    Setting::first()->update(['email_verification' => true]);
    Notification::fake();

    $expired = PendingUser::create([
        'role' => 'candidate',
        'name' => 'Expired Candidate',
        'username' => 'expired-candidate',
        'email' => 'expired-candidate@gmail.com',
        'password' => Hash::make('password'),
        'verification_token' => Str::random(60),
        'expires_at' => now()->subDay(),
    ]);

    $this->post(route('register'), [
        'role' => 'candidate',
        'name' => 'Replacement Candidate',
        'email' => 'expired-candidate@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('verification.notice', [
        'email' => 'expired-candidate@gmail.com',
    ]));

    $replacement = PendingUser::where('email', 'expired-candidate@gmail.com')->firstOrFail();

    expect($replacement->id)->not->toBe($expired->id)
        ->and($replacement->name)->toBe('Replacement Candidate')
        ->and($replacement->expires_at->isFuture())->toBeTrue();
});

test('a superadmin can view and resend a pending verification', function () {
    Notification::fake();

    $pendingUser = PendingUser::create([
        'role' => 'company',
        'name' => 'Pending Employer',
        'username' => 'pending-employer',
        'email' => 'pending-employer@gmail.com',
        'company_registration_number' => 'PENDING-001',
        'password' => Hash::make('password'),
        'verification_token' => Str::random(60),
        'expires_at' => now()->subHour(),
    ]);
    $oldToken = $pendingUser->verification_token;

    actingAs(createAdmin(), 'admin')
        ->get(route('pending-registration.index'))
        ->assertOk()
        ->assertSee('pending-employer@gmail.com');

    actingAs(createAdmin(), 'admin')
        ->post(route('pending-registration.resend', $pendingUser))
        ->assertRedirect();

    $pendingUser->refresh();

    expect($pendingUser->verification_token)->not->toBe($oldToken)
        ->and($pendingUser->expires_at->isFuture())->toBeTrue()
        ->and($pendingUser->verification_send_attempts)->toBe(1)
        ->and($pendingUser->last_verification_sent_at)->not->toBeNull();
});

test('captcha is required for candidate and employer registration', function (string $role) {
    config(['captcha.active' => true]);

    $payload = [
        'role' => $role,
        'name' => 'Captcha Test',
        'email' => "captcha-{$role}@gmail.com",
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    if ($role === 'company') {
        $payload['company_registration_number'] = 'CAPTCHA-COMPANY-001';
    }

    $this->post(route('register'), $payload)
        ->assertSessionHasErrors('g-recaptcha-response');

    $this->assertDatabaseMissing('pending_users', [
        'email' => $payload['email'],
    ]);
})->with(['candidate', 'company']);
