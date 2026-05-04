<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new
#[Title('Gestion des utilisateurs')]
class extends Component {
    use WithPagination, Toast;

    // Filtres
    public string $search = '';
    public string $roleFilter = '';
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    // Modal formulaire
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $userToDelete = null;

    // Formulaire
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';
    public ?string $phone = '';
    public ?string $specialty = '';
    public bool $is_active = true;

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->roleFilter, fn($q) => $q->where('role', $this->roleFilter))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);
    }

    public function getRolesProperty()
    {
        return [
            ['id' => 'admin', 'name' => 'Administrateur'],
            ['id' => 'medecin', 'name' => 'Médecin'],
            ['id' => 'consultant', 'name' => 'Consultant'],
        ];
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

        $this->validate($rules);

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
            $this->success('Utilisateur modifié avec succès.');
        } else {
            $data['password'] = Hash::make($this->password);
            User::create($data);
            $this->success('Utilisateur créé avec succès.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        if ($id === auth()->id()) {
            $this->error('Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        $this->userToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $user = User::find($this->userToDelete);
        if ($user) {
            $user->delete();
            $this->success('Utilisateur supprimé avec succès.');
        }
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        $this->success($user->is_active ? 'Compte activé.' : 'Compte désactivé.');
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
            'roles' => $this->roles,
        ]);
    }
};

?>

<div>
    {{-- En-tête --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Gestion des utilisateurs</h1>
            <p class="text-base-content/70 mt-1">Administration des comptes de la clinique</p>
        </div>
        <x-button label="Nouvel utilisateur" icon="o-plus" class="btn-primary" wire:click="create" />
    </div>

    {{-- Filtres --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input
                label="Recherche"
                icon="o-magnifying-glass"
                placeholder="Nom ou email..."
                wire:model.live.debounce.300ms="search"
                clearable />

            <x-select
                label="Rôle"
                icon="o-user-group"
                :options="$roles"
                placeholder="Tous les rôles"
                wire:model.live="roleFilter"
                clearable />
        </div>
    </x-card>

    {{-- Tableau --}}
    <x-card>
        <x-table
            :headers="[
                ['key' => 'name', 'label' => 'Nom', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'role', 'label' => 'Rôle'],
                ['key' => 'specialty', 'label' => 'Spécialité'],
                ['key' => 'phone', 'label' => 'Téléphone'],
                ['key' => 'is_active', 'label' => 'Statut'],
                ['key' => 'created_at', 'label' => 'Date création', 'sortable' => true],
                ['key' => 'actions', 'label' => 'Actions'],
            ]"
            :rows="$users"
            :sort-by="$sortBy"
            with-pagination
            striped>

            @scope('cell_role', $user)
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
                <x-badge :value="$roleLabels[$user['role']]" :class="$roleColors[$user['role']] . ' badge-soft'" />
            @endscope

            @scope('cell_specialty', $user)
                <span class="text-sm">{{ $user['specialty'] ?? '-' }}</span>
            @endscope

            @scope('cell_phone', $user)
                <span class="text-sm">{{ $user['phone'] ?? '-' }}</span>
            @endscope

            @scope('cell_is_active', $user)
                <button wire:click="toggleActive({{ $user['id'] }})" class="btn btn-xs btn-ghost">
                    @if($user['is_active'])
                        <span class="badge badge-success badge-sm">Actif</span>
                    @else
                        <span class="badge badge-error badge-sm">Inactif</span>
                    @endif
                </button>
            @endscope

            @scope('cell_created_at', $user)
                {{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y') }}
            @endscope

            @scope('actions', $user)
                <div class="flex gap-1">
                    <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Modifier" wire:click="edit({{ $user['id'] }})" />

                    @if($user['id'] !== auth()->id())
                        <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm text-error"
                            tooltip-left="Supprimer" wire:click="confirmDelete({{ $user['id'] }})" />
                    @endif
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal Formulaire --}}
    <x-modal wire:model="showModal" title="{{ $editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}" size="2xl" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <x-input label="Nom complet" type="text" wire:model="name" required />
                    @error('name') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <x-input label="Email" type="email" wire:model="email" required />
                    @error('email') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <x-select wire:model="role" label="Sélectionner un rôle" :options="$roles"  required />
                </div>

                <div class="form-control">
                    <x-input label="Téléphone" type="tel" wire:model="phone" />
                </div>

                <div class="form-control">
                    <x-input label="Spécialité" type="text" wire:model="specialty"
                        placeholder="Psychiatre, Psychologue, ..." />
                </div>

                <div class="form-control">
                    <x-input label="Mot de passe" type="password" wire:model="password"
                        placeholder="{{ $editingId ? 'Laisser vide pour ne pas modifier' : 'Minimum 8 caractères' }}" />
                    @error('password') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                @if(!$editingId || $password)
                <div class="form-control">
                    <x-input label="Confirmation" type="password" wire:model="password_confirmation" />
                </div>
                @endif

                <div class="form-control col-span-2">
                    <x-checkbox label="Actif" wire:model="is_active" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Annuler" wire:click="$set('showModal', false)" />
                <x-button label="{{ $editingId ? 'Modifier' : 'Créer' }}" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Confirmation Suppression --}}
    <x-modal wire:model="showDeleteModal" title="Confirmation" separator>
        <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('showDeleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="delete" spinner="delete" />
        </x-slot:actions>
    </x-modal>
</div>
