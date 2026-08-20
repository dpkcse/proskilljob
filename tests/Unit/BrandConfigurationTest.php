<?php

uses(Tests\TestCase::class);

use App\Notifications\EmailVerifyNotification;

it('uses NAXAS as the safe default product brand', function () {
    config()->set('brand.product_name', 'NAXAS');

    expect(config('brand.product_name'))->toBe('NAXAS')
        ->and(config('brand.company_name'))->toBe('NAXAS AI')
        ->and(config('brand.product_attribution'))->toBe('A Product of NAXAS AI');
});

it('uses the configured product name in the verification email footer', function () {
    config()->set('brand.product_name', 'NAXAS');

    $mail = (new EmailVerifyNotification('candidate@example.test', 'verification-token'))
        ->toMail(new stdClass);

    expect($mail->salutation)->toBe('Best regards, NAXAS Team');
});

it('defines the PWA manifest with the configured application name', function () {
    config()->set('app.name', 'NAXAS');

    expect(config('app.name'))->toBe('NAXAS');
});
