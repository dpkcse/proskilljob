<?php

use Illuminate\Support\Facades\Artisan;

it('can inspect application routes when firebase credentials are not configured', function () {
    config([
        'firebase.projects.app.credentials' => null,
    ]);

    expect(Artisan::call('route:list', ['--json' => true]))->toBe(0);
});

it('does not initialize firebase while storing an anonymous device token', function () {
    config([
        'firebase.projects.app.credentials' => null,
    ]);

    $this->postJson('/api/store-token-anonymous-user', [
        'token' => 'device-token-without-firebase',
    ])->assertOk()
        ->assertJsonPath('data.message', 'Token successfully stored!');
});

it('returns a controlled response when social authentication is not configured', function () {
    config([
        'firebase.projects.app.credentials' => null,
    ]);

    $this->postJson('/api/social-media-authentication', [
        'firebaseToken' => 'invalid-without-firebase-configuration',
        'provider' => 'google',
        'actionKey' => 'login',
    ])->assertStatus(503)
        ->assertJsonPath('data.message', 'Social authentication is temporarily unavailable.');
});
