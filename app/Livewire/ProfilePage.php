<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProfilePage extends Component
{
    use InteractsWithCart;

    public string $tag = 'Casa';
    public string $street = '';
    public string $exterior_number = '';
    public string $interior_number = '';
    public string $neighborhood = '';
    public string $zip_code = '';
    public string $references = '';
    public bool $showAddressForm = false;
    public ?string $profileMessage = null;

    public function saveAddress(): void
    {
        $this->validate([
            'tag' => 'required|string|max:50',
            'street' => 'required|string|max:255',
            'exterior_number' => 'required|string|max:30',
            'neighborhood' => 'required|string|max:120',
            'zip_code' => 'required|string|max:10',
        ]);

        $isFirst = Auth::user()->addresses()->count() === 0;

        Address::create([
            'user_id' => Auth::id(),
            'tag' => $this->tag,
            'street' => $this->street,
            'exterior_number' => $this->exterior_number,
            'interior_number' => $this->interior_number ?: null,
            'neighborhood' => $this->neighborhood,
            'zip_code' => $this->zip_code,
            'references' => $this->references ?: null,
            'is_default' => $isFirst,
        ]);

        $this->reset(['street', 'exterior_number', 'interior_number', 'neighborhood', 'zip_code', 'references', 'showAddressForm']);
        $this->profileMessage = 'Dirección guardada en tu libreta.';
    }

    public function setDefaultAddress(int $addressId): void
    {
        Address::where('user_id', Auth::id())->update(['is_default' => false]);
        Address::where('user_id', Auth::id())->where('id', $addressId)->update(['is_default' => true]);
        $this->profileMessage = 'Dirección predeterminada actualizada.';
    }

    public function deleteAddress(int $addressId): void
    {
        Address::where('user_id', Auth::id())->where('id', $addressId)->delete();
        $this->profileMessage = 'Dirección eliminada.';
    }

    public function render()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $recentOrders = $user->orders()->with('items')->latest()->take(5)->get();

        return view('livewire.profile-page', [
            'user' => $user,
            'addresses' => $addresses,
            'recentOrders' => $recentOrders,
        ]);
    }
}

