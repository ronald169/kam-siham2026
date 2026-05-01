<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new
#[Title('Gestion des utilisateurs')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // Formulaire
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';
    public ?string $phone = '';
    public ?string $specialty = '';
    public bool $is_active = true;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->editingId,
            'role' => 'required|in:admin,medecin,consultant',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->roleFilter, fn($q) => $q->where('role', $this->roleFilter))
            ->latest()
            ->paginate(15);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->phone = $user->phone;
        $this->specialty = $user->specialty;
        $this->is_active = $user->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'specialty' => $this->specialty,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            session()->flash('success', 'Utilisateur modifié avec succès.');
        } else {
            $data['password'] = Hash::make($this->password);
            User::create($data);
            session()->flash('success', 'Utilisateur créé avec succès.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        session()->flash('success', $user->is_active ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'password_confirmation', 'role', 'phone', 'specialty']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'users' => $this->users,
        ]);
    }
};

?>

<div>
    {{-- En-tête --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Utilisateurs</h1>
            <p class="text-base-content/70 mt-1">Gestion des comptes de la clinique</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvel utilisateur
        </button>
    </div>

    {{-- Filtres --}}
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="input input-bordered w-full" />

                <select wire:model.live="roleFilter" class="select select-bordered w-full">
                    <option value="">Tous les rôles</option>
                    <option value="admin">Administrateur</option>
                    <option value="medecin">Médecin</option>
                    <option value="consultant">Consultant</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Spécialité</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="font-medium">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @php
                                $roleColors = [
                                    'admin' => 'badge-error',
                                    'medecin' => 'badge-success',
                                    'consultant' => 'badge-info',
                                ];
                                $roleLabels = [
                                    'admin' => 'Administrateur',
                                    'medecin' => 'Médecin',
                                    'consultant' => 'Consultant',
                                ];
                            @endphp
                            <span class="badge {{ $roleColors[$user->role] }} badge-sm">{{ $roleLabels[$user->role] }}</span>
                        </td>
                        <td>{{ $user->specialty ?? '-' }}</td>
                        <td>
                            <button wire:click="toggleActive({{ $user->id }})" class="btn btn-xs btn-ghost">
                                @if($user->is_active)
                                    <span class="badge badge-success badge-sm">Actif</span>
                                @else
                                    <span class="badge badge-error badge-sm">Inactif</span>
                                @endif
                            </button>
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                @if($user->id !== auth()->id())
                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Supprimer cet utilisateur ?" class="btn btn-sm btn-ghost text-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">Aucun utilisateur trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-actions justify-end p-4">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal Formulaire --}}
    <dialog class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="font-bold text-lg mb-4">{{ $editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Nom complet *</span></label>
                        <input type="text" wire:model="name" class="input input-bordered" required />
                        @error('name') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email *</span></label>
                        <input type="email" wire:model="email" class="input input-bordered" required />
                        @error('email') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Rôle *</span></label>
                        <select wire:model="role" class="select select-bordered" required>
                            <option value="">Sélectionner</option>
                            <option value="admin">Administrateur</option>
                            <option value="medecin">Médecin</option>
                            <option value="consultant">Consultant</option>
                        </select>
                        @error('role') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Téléphone</span></label>
                        <input type="tel" wire:model="phone" class="input input-bordered" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Spécialité</span></label>
                        <input type="text" wire:model="specialty" class="input input-bordered"
                            placeholder="Psychiatre, Psychologue, ..." />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mot de passe</span></label>
                        <input type="password" wire:model="password" class="input input-bordered"
                            placeholder="{{ $editingId ? 'Laisser vide pour ne pas modifier' : 'Minimum 8 caractères' }}" />
                        @error('password') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    @if(!$editingId || $password)
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Confirmation</span></label>
                        <input type="password" wire:model="password_confirmation" class="input input-bordered" />
                    </div>
                    @endif

                    <div class="form-control col-span-2">
                        <label class="cursor-pointer label justify-start gap-3">
                            <input type="checkbox" wire:model="is_active" class="checkbox checkbox-primary" />
                            <span class="label-text">Compte actif</span>
                        </label>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-ghost">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button wire:click="$set('showModal', false)">Fermer</button>
        </form>
    </dialog>
</div>
