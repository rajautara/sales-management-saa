<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Livewire\Volt\Volt;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('new users can register', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('company_name', 'Test Company')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('registration screen is unavailable when public registration is disabled', function () {
    config(['auth.registration_enabled' => false]);

    $this->get('/register')->assertNotFound();
});

test('registration links are hidden when public registration is disabled', function () {
    config(['auth.registration_enabled' => false]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Get Started')
        ->assertDontSee('Start Free Trial')
        ->assertDontSee(route('register', absolute: false), false);
});

test('direct registration action cannot create company or user when disabled', function () {
    config(['auth.registration_enabled' => false]);

    expect(fn () => Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('company_name', 'Test Company')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
    )->toThrow(NotFoundHttpException::class);

    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
    expect(Company::where('name', 'Test Company')->exists())->toBeFalse();
});
