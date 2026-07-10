<?php

use Illuminate\Support\Facades\Route;

const SECURITY_HEADERS = [
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-Content-Type-Options' => 'nosniff',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
];

function assertSecurityHeaders($response): void
{
    foreach (SECURITY_HEADERS as $name => $value) {
        $response->assertHeader($name, $value);
    }
}

it('adds security headers to successful responses', function () {
    $response = $this->get('/');

    $response->assertOk();
    assertSecurityHeaders($response);
});

it('adds security headers to redirects', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
    assertSecurityHeaders($response);
});

it('adds security headers to rendered error responses', function () {
    $response = $this->get('/route-that-does-not-exist');

    $response->assertNotFound();
    assertSecurityHeaders($response);
});

it('preserves an existing content security policy', function () {
    Route::get('/security-headers/csp', fn () => response('ok')->header(
        'Content-Security-Policy',
        "default-src 'self'",
    ));

    $response = $this->get('/security-headers/csp');

    $response->assertOk()
        ->assertHeader('Content-Security-Policy', "default-src 'self'");
    assertSecurityHeaders($response);
});
