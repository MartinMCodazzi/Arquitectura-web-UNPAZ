<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('el header invita a registrarse e iniciar sesion', function () {
    $this->get('/productos')
        ->assertOk()
        ->assertSee('Registrate')
        ->assertSee('Inicia Sesión')
        ->assertDontSee('Mi Cuenta');
});

test('el header del cliente logueado muestra su nombre y el menu de cuenta', function () {
    $user = User::factory()->create([
        'name' => 'Juan Pérez',
    ]);

    $this->actingAs($user);

    $this->get('/productos')
        ->assertOk()
        ->assertSee('Juan')
        ->assertSee($user->name)
        ->assertSee('Mi cuenta')
        ->assertSee('Cerrar Sesión');

    $this->post(route('logout'))
        ->assertRedirect(route('productos.index'));

    $this->assertGuest();
});

test('el modelo de usuario extrae solo el primer nombre', function () {
    $user = new User(['name' => 'Gonzalo Oros']);
    expect($user->first_name)->toBe('Gonzalo');

    $userOnly = new User(['name' => 'Gonzalo']);
    expect($userOnly->first_name)->toBe('Gonzalo');

    $userSpaces = new User(['name' => '  María del Carmen  ']);
    expect($userSpaces->first_name)->toBe('María');
});

test('un cliente que inicia sesion desde el carrito vuelve al checkout', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('redirectTo', 'carrito')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('carrito.index', absolute: false));

    $this->assertAuthenticated();

    expect(session('open_checkout'))->toBeTrue();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('productos.index', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
