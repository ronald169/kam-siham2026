<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Patient;
use App\Models\User;

new
#[Title('Patients')]
class extends Component {
    use WithPagination, Toast;

    // Filtres et recherche
    public string $search = '';
    public string $status = '';
    public string $sex = '';
    public string $doctor = '';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // Modal de suppression
    public bool $deleteModal = false;
    public ?int $patientToDelete = null;

    public function getCanEditProperty()
    {
        return auth()->user()->isAdmin() || auth()->user()->isMedecin();
    }

    public function getCanDeleteProperty()
    {
        return auth()->user()->isAdmin();
    }

    public function getDoctorsProperty()
    {
        return User::where('role', 'medecin')->get();
    }

    public function getStatusesProperty()
    {
        return [
            ['id' => 'active', 'name' => 'Actif'],
            ['id' => 'discharged', 'name' => 'Sorti'],
            ['id' => 'transferred', 'name' => 'Transféré'],
        ];
    }

    public function getSexesProperty()
    {
        return [
            ['id' => 'Homme', 'name' => 'Homme'],
            ['id' => 'Femme', 'name' => 'Femme'],
        ];
    }

    public function getPatientsProperty()
    {
        return Patient::query()
            ->withAggregate('referringDoctor', 'name')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('medical_record_number', 'like', '%' . $this->search . '%')
                      ->orWhere('patient_phone', 'like', '%' . $this->search . '%');
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sex, fn($q) => $q->where('sex', $this->sex))
            ->when($this->doctor, fn($q) => $q->where('referring_doctor_id', $this->doctor))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);
    }

    public function deletePatient($id)
    {
        if (!auth()->user()->isAdmin()) {
            $this->error('Vous n\'avez pas les droits pour supprimer un patient.');
            return;
        }

        $this->patientToDelete = $id;
        $this->deleteModal = true;
    }

    public function confirmDelete()
    {
        $patient = Patient::find($this->patientToDelete);
        if ($patient) {
            $patientName = $patient->name;
            $patient->delete();
            $this->success("Patient '$patientName' supprimé avec succès.");
        }
        $this->deleteModal = false;
        $this->patientToDelete = null;
    }

    public function render()
    {
        return $this->view([
            'patients' => $this->patients,
            'doctors' => $this->doctors,
            'statuses' => $this->statuses,
            'sexes' => $this->sexes,
        ]);
    }
};

?>

<div>
    {{-- En-tête --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Patients</h1>
            <p class="text-base-content/70 mt-1">Gestion des dossiers patients</p>
        </div>
        @if($this->canEdit)
            <x-button label="Nouveau patient" icon="o-plus" class="btn-primary" link="{{ route('patients.create') }}" />
        @endif
    </div>

    {{-- Filtres --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Recherche --}}
            <x-input
                label="Recherche"
                icon="o-magnifying-glass"
                placeholder="Nom, N° dossier, Téléphone..."
                wire:model.live.debounce.300ms="search"
                clearable />

            {{-- Filtre statut --}}
            <x-select
                label="Statut"
                icon="o-flag"
                :options="$statuses"
                placeholder="Tous les statuts"
                wire:model.live="status"
                clearable />

            {{-- Filtre sexe --}}
            <x-select
                label="Sexe"
                icon="o-user"
                :options="$sexes"
                placeholder="Tous"
                wire:model.live="sex"
                clearable />

            {{-- Filtre médecin --}}
            <x-select
                label="Médecin référant"
                icon="o-user-group"
                :options="$doctors"
                option-value="id"
                option-label="name"
                placeholder="Tous les médecins"
                wire:model.live="doctor"
                clearable />
        </div>
    </x-card>

    {{-- Tableau des patients --}}
    <x-card>
        <x-table
            :headers="[
                ['key' => 'medical_record_number', 'label' => 'N° Dossier', 'sortable' => true],
                ['key' => 'name', 'label' => 'Nom complet', 'sortable' => true],
                ['key' => 'sex', 'label' => 'Sexe'],
                ['key' => 'age', 'label' => 'Âge'],
                ['key' => 'patient_phone', 'label' => 'Téléphone'],
                ['key' => 'referring_doctor_name', 'label' => 'Médecin réf.',],
                ['key' => 'status', 'label' => 'Statut'],
                ['key' => 'admission_date', 'label' => 'Date admission', 'sortable' => true],
            ]"
            :rows="$patients"
            :sort-by="$sortBy"
            with-pagination
            striped
            link="/patients/{id}">

            {{-- Cellule sexe --}}
            @scope('cell_sex', $patient)
                @if($patient['sex'] === 'Homme')
                    <span class="text-blue-500">{{ $patient['sex'] }}</span>
                @else
                <span class="text-pink-500">{{ $patient['sex'] }}</span>
                @endif
            @endscope

            {{-- Cellule âge --}}
            @scope('cell_age', $patient)
                {{ $patient['age'] ?? '-' }} ans
            @endscope

            {{-- Cellule téléphone --}}
            @scope('cell_patient_phone', $patient)
                @if($patient['patient_phone'])
                    {{ $patient['patient_phone'] }}
                @else
                    -
                @endif
            @endscope

            {{-- Cellule statut --}}
            @scope('cell_status', $patient)
                @if($patient['status'] === 'active')
                    <x-badge value="Actif" class="badge-success badge-soft" />
                @elseif($patient['status'] === 'discharged')
                    <x-badge value="Sorti" class="badge-info badge-soft" />
                @else
                    <x-badge value="Transféré" class="badge-warning badge-soft" />
                @endif
            @endscope

            {{-- Cellule date admission --}}
            @scope('cell_admission_date', $patient)
                {{ $patient['admission_date'] ? \Carbon\Carbon::parse($patient['admission_date'])->format('d/m/Y') : '-' }}
            @endscope

            {{-- Actions --}}
            @scope('actions', $patient)
                <div class="flex gap-1">
                    @if($this->canEdit)
                        <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm"
                            tooltip-left="Modifier"
                            link="{{ route('patients.edit', $patient['id']) }}" />
                    @endif

                    @if($this->canDelete)
                        <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm"
                            tooltip-left="Supprimer"
                            wire:click="deletePatient({{ $patient['id'] }})" />
                    @endif
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal de confirmation de suppression --}}
    <x-modal wire:model="deleteModal" title="Confirmation" separator>
        <p>Êtes-vous sûr de vouloir supprimer ce patient ? Cette action est irréversible.</p>

        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('deleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="confirmDelete" spinner />
        </x-slot:actions>
    </x-modal>
</div>
