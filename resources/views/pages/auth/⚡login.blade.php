<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new
#[Layout('layouts.guest')]
#[Title('Connexion')]
class extends Component {
    use Toast;

    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }

    protected function messages()
    {
        return [
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'Veuillez entrer une adresse email valide',
            'password.required' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
        ];
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();

            // Message de succès MaryUI
            $this->success('Connexion réussie !', position: 'toast-bottom');

            if ($user->isAdmin()) {
                return $this->redirectRoute('dashboard');
            } elseif ($user->isMedecin()) {
                return $this->redirectRoute('dashboard');
            } elseif ($user->isConsultant()) {
                return $this->redirectRoute('dashboard');
            }
        }

        $this->error('Identifiants invalides', position: 'toast-bottom');
        $this->addError('email', 'Les identifiants fournis ne correspondent pas à nos enregistrements.');
    }

};

?>

<div>
    {{-- Carte principale --}}
    <x-card title="Clinique Kam-Siham" subtitle="Connectez-vous à votre compte" shadow separator class="w-full min-w-[480px]">

        {{-- Formulaire --}}
        <x-form wire:submit="login">

            {{-- Email --}}
            <x-input
                label="Email"
                icon="o-envelope"
                placeholder="admin@kam-siham.com"
                wire:model="email"
                required />

            {{-- Mot de passe --}}
            <x-input
                label="Mot de passe"
                icon="o-key"
                type="password"
                placeholder="••••••••"
                wire:model="password"
                required
                class="mt-4" />

            {{-- Se souvenir de moi --}}
            <x-checkbox
                label="Se souvenir de moi"
                wire:model="remember"
                class="mt-4" />

            {{-- Bouton de connexion --}}
            <x-slot:actions>
                <x-button
                    label="Se connecter"
                    type="submit"
                    icon="o-arrow-right-end-on-rectangle"
                    class="btn-primary w-full mt-6"
                    spinner="login" />
            </x-slot:actions>
        </x-form>

        {{-- Comptes de démonstration --}}
        <x-alert title="Comptes de démonstration" icon="o-information-circle" class="alert-info mt-6 text-sm">
            <ul class="list-disc list-inside text-xs mt-2 space-y-1">
                <li>📧 <span class="font-mono">admin@kam-siham.com</span> / password <span class="badge badge-xs badge-error ml-1">Admin</span></li>
                <li>📧 <span class="font-mono">jean.mbarga@kam-siham.com</span> / password <span class="badge badge-xs badge-success ml-1">Médecin</span></li>
                <li>📧 <span class="font-mono">consultant1@kam-siham.com</span> / password <span class="badge badge-xs badge-info ml-1">Consultant</span></li>
            </ul>
        </x-alert>
    </x-card>
</div>
