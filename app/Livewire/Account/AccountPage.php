<?php

namespace App\Livewire\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Panel "Mi cuenta": datos del cliente, domicilios, cuentas y reservas.
 */
#[Layout('layouts.store', [
    'title' => 'Mi cuenta',
    'description' => 'Consultá y actualizá tus domicilios, cuentas y reservas.',
])]
class AccountPage extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Domicilios
    |--------------------------------------------------------------------------
    */

    public bool $showAddressForm = false;

    public ?int $editingAddressId = null;

    public string $addressLabel = 'Casa';

    public string $addressRecipient = '';

    public string $addressPhone = '';

    public string $addressStreet = '';

    public string $addressNumber = '';

    public string $addressFloor = '';

    public string $addressCity = '';

    public string $addressProvince = '';

    public string $addressPostalCode = '';

    public string $addressNotes = '';

    public bool $addressDefault = false;

    /*
    |--------------------------------------------------------------------------
    | Cuentas
    |--------------------------------------------------------------------------
    */

    public bool $showAccountForm = false;

    public ?int $editingAccountId = null;

    public string $accountLabel = 'Cuenta principal';

    public string $accountType = 'banco';

    public string $accountBank = '';

    public string $accountHolder = '';

    public string $accountDni = '';

    public string $accountNumber = '';

    public bool $accountDefault = false;

    public function mount(): void
    {
        $this->addressRecipient = Auth::user()->name;
        $this->accountHolder = Auth::user()->name;
    }

    /*
    |--------------------------------------------------------------------------
    | Domicilios: acciones
    |--------------------------------------------------------------------------
    */

    public function openAddressForm(): void
    {
        $this->resetAddressForm();
        $this->showAddressForm = true;
    }

    public function editAddress(int $id): void
    {
        $address = Auth::user()->addresses()->findOrFail($id);

        $this->editingAddressId = $address->id;
        $this->addressLabel = $address->label;
        $this->addressRecipient = $address->recipient_name;
        $this->addressPhone = $address->phone;
        $this->addressStreet = $address->street;
        $this->addressNumber = $address->number;
        $this->addressFloor = (string) $address->floor;
        $this->addressCity = $address->city;
        $this->addressProvince = $address->province;
        $this->addressPostalCode = $address->postal_code;
        $this->addressNotes = (string) $address->notes;
        $this->addressDefault = $address->is_default;
        $this->showAddressForm = true;
        $this->resetValidation();
    }

    public function cancelAddressForm(): void
    {
        $this->showAddressForm = false;
        $this->resetAddressForm();
    }

    public function saveAddress(): void
    {
        $validated = $this->validate([
            'addressLabel' => ['required', 'string', 'max:40'],
            'addressRecipient' => ['required', 'string', 'min:3', 'max:100'],
            'addressPhone' => ['required', 'string', 'max:30'],
            'addressStreet' => ['required', 'string', 'max:120'],
            'addressNumber' => ['required', 'string', 'max:20'],
            'addressFloor' => ['nullable', 'string', 'max:20'],
            'addressCity' => ['required', 'string', 'max:80'],
            'addressProvince' => ['required', 'string', 'max:80'],
            'addressPostalCode' => ['required', 'string', 'max:10'],
            'addressNotes' => ['nullable', 'string', 'max:500'],
            'addressDefault' => ['boolean'],
        ], $this->addressMessages());

        $user = Auth::user();
        $wasDefault = $this->editingAddressId
            ? (bool) $user->addresses()->whereKey($this->editingAddressId)->value('is_default')
            : false;

        $isDefault = $this->addressDefault || $wasDefault || ! $user->addresses()->exists();

        if ($isDefault) {
            $others = $user->addresses();

            if ($this->editingAddressId) {
                $others->whereKeyNot($this->editingAddressId);
            }

            $others->update(['is_default' => false]);
        }

        $data = [
            'label' => $validated['addressLabel'],
            'recipient_name' => $validated['addressRecipient'],
            'phone' => $validated['addressPhone'],
            'street' => $validated['addressStreet'],
            'number' => $validated['addressNumber'],
            'floor' => $validated['addressFloor'] ?: null,
            'city' => $validated['addressCity'],
            'province' => $validated['addressProvince'],
            'postal_code' => $validated['addressPostalCode'],
            'notes' => $validated['addressNotes'] ?: null,
            'is_default' => $isDefault,
        ];

        if ($this->editingAddressId) {
            $user->addresses()->whereKey($this->editingAddressId)->update($data);
        } else {
            $user->addresses()->create($data);
        }

        $this->showAddressForm = false;
        $this->resetAddressForm();
        $this->dispatch('domicilio-guardado');
    }

    public function setDefaultAddress(int $id): void
    {
        $user = Auth::user();

        $user->addresses()->whereKeyNot($id)->update(['is_default' => false]);
        $user->addresses()->whereKey($id)->update(['is_default' => true]);
    }

    public function deleteAddress(int $id): void
    {
        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);
        $wasDefault = $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $user->addresses()->orderBy('id')->first()?->update(['is_default' => true]);
        }

        if ($this->editingAddressId === $id) {
            $this->cancelAddressForm();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cuentas: acciones
    |--------------------------------------------------------------------------
    */

    public function openAccountForm(): void
    {
        $this->resetAccountForm();
        $this->showAccountForm = true;
    }

    public function editAccount(int $id): void
    {
        $account = Auth::user()->accounts()->findOrFail($id);

        $this->editingAccountId = $account->id;
        $this->accountLabel = $account->label;
        $this->accountType = $account->type;
        $this->accountBank = (string) $account->bank_name;
        $this->accountHolder = $account->holder_name;
        $this->accountDni = $account->holder_dni;
        $this->accountNumber = $account->cbu_alias;
        $this->accountDefault = $account->is_default;
        $this->showAccountForm = true;
        $this->resetValidation();
    }

    public function cancelAccountForm(): void
    {
        $this->showAccountForm = false;
        $this->resetAccountForm();
    }

    public function saveAccount(): void
    {
        $validated = $this->validate([
            'accountLabel' => ['required', 'string', 'max:40'],
            'accountType' => ['required', Rule::in(['banco', 'billetera'])],
            'accountBank' => ['nullable', 'string', 'max:80'],
            'accountHolder' => ['required', 'string', 'min:3', 'max:100'],
            'accountDni' => ['required', 'string', 'regex:/^\d{7,9}$/'],
            'accountNumber' => ['required', 'string', 'min:6', 'max:60'],
            'accountDefault' => ['boolean'],
        ], $this->accountMessages());

        $user = Auth::user();
        $wasDefault = $this->editingAccountId
            ? (bool) $user->accounts()->whereKey($this->editingAccountId)->value('is_default')
            : false;

        $isDefault = $this->accountDefault || $wasDefault || ! $user->accounts()->exists();

        if ($isDefault) {
            $others = $user->accounts();

            if ($this->editingAccountId) {
                $others->whereKeyNot($this->editingAccountId);
            }

            $others->update(['is_default' => false]);
        }

        $data = [
            'label' => $validated['accountLabel'],
            'type' => $validated['accountType'],
            'bank_name' => $validated['accountBank'] ?: null,
            'holder_name' => $validated['accountHolder'],
            'holder_dni' => $validated['accountDni'],
            'cbu_alias' => $validated['accountNumber'],
            'is_default' => $isDefault,
        ];

        if ($this->editingAccountId) {
            $user->accounts()->whereKey($this->editingAccountId)->update($data);
        } else {
            $user->accounts()->create($data);
        }

        $this->showAccountForm = false;
        $this->resetAccountForm();
        $this->dispatch('cuenta-guardada');
    }

    public function setDefaultAccount(int $id): void
    {
        $user = Auth::user();

        $user->accounts()->whereKeyNot($id)->update(['is_default' => false]);
        $user->accounts()->whereKey($id)->update(['is_default' => true]);
    }

    public function deleteAccount(int $id): void
    {
        $user = Auth::user();
        $account = $user->accounts()->findOrFail($id);
        $wasDefault = $account->is_default;

        $account->delete();

        if ($wasDefault) {
            $user->accounts()->orderBy('id')->first()?->update(['is_default' => true]);
        }

        if ($this->editingAccountId === $id) {
            $this->cancelAccountForm();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Formularios
    |--------------------------------------------------------------------------
    */

    private function resetAddressForm(): void
    {
        $this->editingAddressId = null;
        $this->addressLabel = 'Casa';
        $this->addressRecipient = Auth::user()->name;
        $this->addressPhone = '';
        $this->addressStreet = '';
        $this->addressNumber = '';
        $this->addressFloor = '';
        $this->addressCity = '';
        $this->addressProvince = '';
        $this->addressPostalCode = '';
        $this->addressNotes = '';
        $this->addressDefault = false;
        $this->resetValidation();
    }

    private function resetAccountForm(): void
    {
        $this->editingAccountId = null;
        $this->accountLabel = 'Cuenta principal';
        $this->accountType = 'banco';
        $this->accountBank = '';
        $this->accountHolder = Auth::user()->name;
        $this->accountDni = '';
        $this->accountNumber = '';
        $this->accountDefault = false;
        $this->resetValidation();
    }

    /**
     * @return array<string, string>
     */
    private function addressMessages(): array
    {
        return [
            'addressLabel.required' => 'Ponele un nombre al domicilio (por ejemplo, Casa).',
            'addressRecipient.required' => 'Ingresá el nombre de quien recibe.',
            'addressRecipient.min' => 'El nombre debe tener al menos 3 caracteres.',
            'addressPhone.required' => 'Ingresá un teléfono de contacto.',
            'addressStreet.required' => 'Ingresá la calle del domicilio.',
            'addressNumber.required' => 'Ingresá el número del domicilio.',
            'addressCity.required' => 'Ingresá la ciudad.',
            'addressProvince.required' => 'Ingresá la provincia.',
            'addressPostalCode.required' => 'Ingresá el código postal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function accountMessages(): array
    {
        return [
            'accountLabel.required' => 'Ponele un nombre a la cuenta.',
            'accountHolder.required' => 'Ingresá el nombre del titular.',
            'accountHolder.min' => 'El nombre del titular es demasiado corto.',
            'accountDni.required' => 'Ingresá el DNI del titular.',
            'accountDni.regex' => 'El DNI debe tener entre 7 y 9 dígitos.',
            'accountNumber.required' => 'Ingresá el CBU o alias de la cuenta.',
            'accountNumber.min' => 'El CBU o alias es demasiado corto.',
        ];
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.account.account-page', [
            'addresses' => $user->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get(),
            'accounts' => $user->accounts()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get(),
            'reservations' => $user->reservations()
                ->with('items')
                ->latest()
                ->get(),
        ]);
    }
}
