<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;

new
#[Title('Rendez-vous')]
class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public ?int $patient_id = null;
    public string $status = '';
    public string $service_type = '';
    public string $date_filter = '';
    public array $sortBy = ['column' => 'appointment_datetime', 'direction' => 'asc'];

    // Modal formulaire
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $appointmentToDelete = null;
    public ?int $patientId = null;
    public string $appointment_datetime = '';
    public string $serviceType = '';
    public ?string $reason = '';
    public ?string $notes = '';

    public function getPatientsProperty()
    {
        return Patient::orderBy('name')->get(['id', 'name', 'medical_record_number']);
    }

    public function getStatusesProperty()
    {
        return [
            ['id' => 'scheduled', 'name' => 'Programmé'],
            ['id' => 'completed', 'name' => 'Effectué'],
            ['id' => 'cancelled', 'name' => 'Annulé'],
            ['id' => 'no_show', 'name' => 'Non présenté'],
        ];
    }

    public function getServiceTypesProperty()
    {
        return [
            ['id' => 'toxicologie', 'name' => 'Toxicologie'],
            ['id' => 'psychopathologie', 'name' => 'Psychopathologie'],
            ['id' => 'medecine', 'name' => 'Médecine Générale'],
        ];
    }

    public function getAppointmentsProperty()
    {
        $query = Appointment::query()
            ->with(['patient', 'doctor'])
            ->when(auth()->user()->isMedecin(), fn($q) => $q->where('doctor_id', auth()->id()))
            ->when($this->patient_id, fn($q) => $q->where('patient_id', $this->patient_id))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->service_type, fn($q) => $q->where('service_type', $this->service_type))
            ->when($this->date_filter === 'today', fn($q) => $q->whereDate('appointment_datetime', today()))
            ->when($this->date_filter === 'tomorrow', fn($q) => $q->whereDate('appointment_datetime', now()->addDay()))
            ->when($this->date_filter === 'week', fn($q) => $q->whereBetween('appointment_datetime', [now(), now()->addWeek()]))
            ->when($this->search, fn($q) => $q->whereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate(15);
    }

    public function create()
    {
        $this->resetForm();
        $this->appointment_datetime = now()->addDay()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->editingId = $id;
        $this->patientId = $appointment->patient_id;
        $this->appointment_datetime = $appointment->appointment_datetime->format('Y-m-d\TH:i');
        $this->serviceType = $appointment->service_type;
        $this->reason = $appointment->reason;
        $this->notes = $appointment->notes;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'appointment_datetime' => 'required|date',
            'serviceType' => 'required|in:toxicologie,psychopathologie,medecine',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => auth()->id(),
            'appointment_datetime' => $this->appointment_datetime,
            'service_type' => $this->serviceType,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => 'scheduled',
        ];

        if ($this->editingId) {
            $appointment = Appointment::findOrFail($this->editingId);
            $appointment->update($data);
            $this->success('Rendez-vous modifié avec succès.');
        } else {
            Appointment::create($data);
            $this->success('Rendez-vous créé avec succès.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function updateStatus($id, $status)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $status]);

        $labels = [
            'scheduled' => 'programmé',
            'completed' => 'effectué',
            'cancelled' => 'annulé',
            'no_show' => 'non présenté',
        ];

        $this->success("Rendez-vous marqué comme {$labels[$status]}.");
    }

    public function confirmDelete($id)
    {
        $this->appointmentToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $appointment = Appointment::find($this->appointmentToDelete);
        if ($appointment) {
            $appointment->delete();
            $this->success('Rendez-vous supprimé avec succès.');
        }
        $this->showDeleteModal = false;
        $this->appointmentToDelete = null;
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'patientId', 'appointment_datetime', 'serviceType', 'reason', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'patients' => $this->patients,
            'appointments' => $this->appointments,
            'statuses' => $this->statuses,
            'serviceTypes' => $this->serviceTypes,
        ]);
    }
};

?>

<div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Rendez-vous</h1>
            <p class="text-base-content/70 mt-1">Gestion des consultations programmées</p>
        </div>
        <x-button label="Nouveau rendez-vous" icon="o-plus" class="btn-primary" wire:click="create" />
    </div>

    {{-- Filtres --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-choices-offline
                label="Patient"
                wire:model.live="patient_id"
                :options="$patients"
                option-value="id"
                option-label="name"
                placeholder="Tous les patients"
                id="patient_id"
                name="patient_id"
                single
                clearable
                searchable />

            <x-select
                label="Statut"
                wire:model.live="status"
                :options="$statuses"
                option-value="id"
                option-label="name"
                placeholder="Tous les statuts"
                id="status"
                name="status"
                clearable />

            <x-select
                label="Service"
                wire:model.live="service_type"
                :options="$serviceTypes"
                option-value="id"
                option-label="name"
                placeholder="Tous les services"
                id="service_type"
                name="service_type"
                clearable />

            <x-select
                label="Période"
                wire:model.live="date_filter"
                :options="[
                    ['id' => '', 'name' => 'Toutes les dates'],
                    ['id' => 'today', 'name' => 'Aujourd\'hui'],
                    ['id' => 'tomorrow', 'name' => 'Demain'],
                    ['id' => 'week', 'name' => 'Cette semaine'],
                ]"
                option-value="id"
                option-label="name"
                id="date_filter"
                name="date_filter"
                clearable />

            <x-input
                label="Recherche"
                icon="o-magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="Nom du patient"
                clearable />
        </div>
    </x-card>

    {{-- Tableau --}}
    <x-card>
        <x-table
            :headers="[
                ['key' => 'appointment_datetime', 'label' => 'Date & Heure', 'sortable' => true],
                ['key' => 'patient.name', 'label' => 'Patient'],
                ['key' => 'patient.medical_record_number', 'label' => 'N° Dossier'],
                ['key' => 'service_type', 'label' => 'Service'],
                ['key' => 'reason', 'label' => 'Motif'],
                ['key' => 'doctor.name', 'label' => 'Médecin'],
                ['key' => 'status', 'label' => 'Statut'],
                ['key' => 'actions', 'label' => 'Actions'],
            ]"
            :rows="$appointments"
            :sort-by="$sortBy"
            with-pagination
            striped>

            @scope('cell_appointment_datetime', $appointment)
                <div class="font-medium">{{ \Carbon\Carbon::parse($appointment['appointment_datetime'])->format('d/m/Y') }}</div>
                <div class="text-xs text-base-content/60">{{ \Carbon\Carbon::parse($appointment['appointment_datetime'])->format('H:i') }}</div>
            @endscope

            @scope('cell_service_type', $appointment)
                @php
                    $serviceLabels = [
                        'toxicologie' => 'Toxicologie',
                        'psychopathologie' => 'Psychopathologie',
                        'medecine' => 'Médecine',
                    ];
                @endphp
                <x-badge :value="$serviceLabels[$appointment['service_type']]" class="badge-info badge-soft" />
            @endscope

            @scope('cell_status', $appointment)
                @php
                    $statusClasses = [
                        'scheduled' => 'badge-warning',
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-error',
                        'no_show' => 'badge-neutral',
                    ];
                    $statusLabels = [
                        'scheduled' => 'Programmé',
                        'completed' => 'Effectué',
                        'cancelled' => 'Annulé',
                        'no_show' => 'Non présenté',
                    ];
                @endphp
                <x-badge :value="$statusLabels[$appointment['status']]" :class="$statusClasses[$appointment['status']] . ' badge-soft'" />
            @endscope

            @scope('actions', $appointment)
                <div class="flex gap-1">
                    {{-- Actions rapides selon statut --}}
                    @if($appointment['status'] === 'scheduled')
                        <x-button icon="o-check-circle" class="btn-circle btn-ghost btn-sm btn-success"
                            tooltip-left="Marquer effectué"
                            wire:click="updateStatus({{ $appointment['id'] }}, 'completed')" />
                        <x-button icon="o-x-circle" class="btn-circle btn-ghost btn-sm btn-error"
                            tooltip-left="Annuler"
                            wire:click="updateStatus({{ $appointment['id'] }}, 'cancelled')" />
                    @endif

                    {{-- Actions standard --}}
                    <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Modifier" wire:click="edit({{ $appointment['id'] }})" />
                    <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Supprimer" wire:click="confirmDelete({{ $appointment['id'] }})" />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal Formulaire --}}
    <x-modal wire:model="showModal" title="{{ $editingId ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous' }}" size="2xl" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-choices-offline
                    label="Patient"
                    wire:model="patientId"
                    :options="$patients"
                    option-value="id"
                    option-label="name"
                    required
                    id="patientId"
                    name="patientId"
                    single
                    clearable
                    searchable />

                <x-select
                    label="Service"
                    wire:model="serviceType"
                    :options="$serviceTypes"
                    option-value="id"
                    option-label="name"
                    required
                    id="serviceType"
                    name="serviceType" />

                <div class="form-control md:col-span-2">
                    <x-datetime label="Date et heure" type="datetime-local" wire:model="appointment_datetime" required />
                    @error('appointment_datetime') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="form-control md:col-span-2">
                    <x-textarea label="Motif" wire:model="reason" class="textarea textarea-bordered" rows="2" placeholder="Raison de la consultation..."></x-textarea>
                </div>

                <div class="form-control md:col-span-2">
                    <x-textarea label="Notes" wire:model="notes" placeholder="Informations complémex-textarea" />
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
        <p>Êtes-vous sûr de vouloir supprimer ce rendez-vous ? Cette action est irréversible.</p>
        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('showDeleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="delete" spinner="delete" />
        </x-slot:actions>
    </x-modal>
</div>
