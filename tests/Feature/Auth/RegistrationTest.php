<?php

namespace Tests\Feature\Auth;

use Livewire\Volt\Volt;

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
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('productos.index', absolute: false));

    $this->assertAuthenticated();
});

test('new users that register from the cart return to finish the purchase', function () {
    $component = Volt::test('pages.auth.register')
        ->set('redirectTo', 'carrito')
        ->set('name', 'Test User')
        ->set('email', 'cart@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('carrito.index', absolute: false));

    $this->assertAuthenticated();

    expect(session('open_checkout'))->toBeTrue();
});
