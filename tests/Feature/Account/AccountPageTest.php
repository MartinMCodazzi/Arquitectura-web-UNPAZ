<?php

use App\Livewire\Account\AccountPage;
use App\Models\User;
use Livewire\Livewire;

test('un invitado no puede entrar a mi cuenta', function () {
    $this->get(route('mi-cuenta'))
        ->assertRedirect(route('login'));
});

test('mi cuenta muestra domicilios, cuentas y reservas', function () {
    $user = User::factory()->create();

    $user->addresses()->create([
        'label' => 'Casa',
        'recipient_name' => 'Ana Gamer',
        'phone' => '1123456789',
        'street' => 'Av. Corrientes',
        'number' => '1234',
        'city' => 'CABA',
        'province' => 'Buenos Aires',
        'postal_code' => '1043',
        'is_default' => true,
    ]);

    $user->accounts()->create([
        'label' => 'Cuenta sueldo',
        'type' => 'banco',
        'bank_name' => 'Banco Nación',
        'holder_name' => 'Ana Gamer',
        'holder_dni' => '20123456',
        'cbu_alias' => 'ana.gamer',
        'is_default' => true,
    ]);

    $reservation = $user->reservations()->create([
        'number' => 'ORD-20261105-TEST',
        'status' => 'reservada',
        'payment_method' => 'transferencia',
        'payment_label' => 'Transferencia bancaria',
        'shipping_method' => 'domicilio',
        'shipping_summary' => 'Envío a domicilio con OCA',
        'subtotal' => 10_000,
        'taxes' => 2_100,
        'shipping_cost' => 0,
        'discount' => 0,
        'total' => 12_100,
        'customer_name' => 'Ana Gamer',
        'customer_email' => 'ana@example.com',
    ]);

    $reservation->items()->create([
        'product_name' => 'Teclado Gamer',
        'unit_price' => 10_000,
        'quantity' => 1,
        'subtotal' => 10_000,
    ]);

    $this->actingAs($user)
        ->get(route('mi-cuenta'))
        ->assertOk()
        ->assertSee('Mi cuenta')
        ->assertSee('Av. Corrientes')
        ->assertSee('20123456')
        ->assertSee('ORD-20261105-TEST')
        ->assertSee('Reservada')
        ->assertSee('Teclado Gamer')
        ->assertSee('Cambiar o agregar domicilio')
        ->assertSee('Cambiar o agregar cuenta');
});

test('un cliente puede agregar un domicilio', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AccountPage::class)
        ->call('openAddressForm')
        ->set('addressLabel', 'Trabajo')
        ->set('addressRecipient', 'Ana Gamer')
        ->set('addressPhone', '1123456789')
        ->set('addressStreet', 'Av. Corrientes')
        ->set('addressNumber', '1234')
        ->set('addressCity', 'CABA')
        ->set('addressProvince', 'Buenos Aires')
        ->set('addressPostalCode', '1043')
        ->call('saveAddress')
        ->assertHasNoErrors();

    $address = $user->addresses()->first();

    expect($address)->not->toBeNull()
        ->and($address->label)->toBe('Trabajo')
        ->and($address->is_default)->toBeTrue();
});

test('un cliente puede agregar una cuenta con nombre y dni', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AccountPage::class)
        ->call('openAccountForm')
        ->set('accountLabel', 'Cuenta sueldo')
        ->set('accountType', 'banco')
        ->set('accountBank', 'Banco Nación')
        ->set('accountHolder', 'Ana Gamer')
        ->set('accountDni', '20123456')
        ->set('accountNumber', 'ana.gamer')
        ->call('saveAccount')
        ->assertHasNoErrors();

    $account = $user->accounts()->first();

    expect($account)->not->toBeNull()
        ->and($account->holder_name)->toBe('Ana Gamer')
        ->and($account->holder_dni)->toBe('20123456')
        ->and($account->is_default)->toBeTrue();
});

test('el dni de la cuenta se valida', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AccountPage::class)
        ->call('openAccountForm')
        ->set('accountLabel', 'Cuenta sueldo')
        ->set('accountHolder', 'Ana Gamer')
        ->set('accountDni', 'abc')
        ->set('accountNumber', 'ana.gamer')
        ->call('saveAccount')
        ->assertHasErrors(['accountDni']);
});
