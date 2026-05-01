<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;

new
#[Title('Rendez-vous')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public ?int $patient_id = null;
    public string $status = '';
    public string $date_filter = '';
    public array $sortBy = ['column' => 'appointment_datetime', 'direction' => 'asc'];

    // Formulaire
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $patientId = null;
    public string $appointment_datetime = '';
    public string $service_type = '';
    public string $reason = '';
    public string $notes = '';

    public function getPatientsProperty()
    {
        $query = Patient::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name');

        if (auth()->user()->isMedecin()) {
            $query->where('referring_doctor_id', auth()->id());
        }

        return $query->get();
    }

    public function getAppointmentsProperty()
    {
        $query = Appointment::query()
            ->with(['patient', 'doctor'])
            ->when(auth()->user()->role === 'medecin', fn($q) => $q->where('doctor_id', auth()->id()))
            ->when($this->patient_id, fn($q) => $q->where('patient_id', $this->patient_id))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->date_filter === 'today', fn($q) => $q->whereDate('appointment_datetime', today()))
            ->when($this->date_filter === 'tomorrow', fn($q) => $q->whereDate('appointment_datetime', now()->addDay()))
            ->when($this->date_filter === 'week', fn($q) => $q->whereBetween('appointment_datetime', [now(), now()->addWeek()]))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return $query->paginate(15);
    }

    public function getServiceTypesProperty()
    {
        return [
            ['id' => 'toxicologie', 'name' => 'Toxicologie'],
            ['id' => 'psychopathologie', 'name' => 'Psychopathologie'],
            ['id' => 'medecine', 'name' => 'Médecine Générale'],
        ];
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
        $this->service_type = $appointment->service_type;
        $this->reason = $appointment->reason;
        $this->notes = $appointment->notes;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'appointment_datetime' => 'required|date',
            'service_type' => 'required|in:toxicologie,psychopathologie,medecine',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => auth()->id(),
            'appointment_datetime' => $this->appointment_datetime,
            'service_type' => $this->service_type,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => 'scheduled',
        ];

        if ($this->editingId) {
            $appointment = Appointment::findOrFail($this->editingId);
            $appointment->update($data);
            session()->flash('success', 'Rendez-vous modifié avec succès.');
        } else {
            Appointment::create($data);
            session()->flash('success', 'Rendez-vous créé avec succès.');
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

        session()->flash('success', "Rendez-vous marqué comme {$labels[$status]}.");
    }

    public function delete($id)
    {
        Appointment::findOrFail($id)->delete();
        session()->flash('success', 'Rendez-vous supprimé avec succès.');
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'patientId', 'appointment_datetime', 'service_type', 'reason', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'patients' => $this->patients,
            'appointments' => $this->appointments,
            'serviceTypes' => $this->serviceTypes,
            'statuses' => $this->statuses,
        ]);
    }
};

?>

<div>
    {{-- En-tête --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Rendez-vous</h1>
            <p class="text-base-content/70 mt-1">Gestion des consultations programmées</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouveau rendez-vous
        </button>
    </div>

    {{-- Filtres --}}
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select wire:model.live="patient_id" class="select select-bordered w-full">
                    <option value="">Tous les patients</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="status" class="select select-bordered w-full">
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                    @endforeach
                </select>

                <select wire:model.live="date_filter" class="select select-bordered w-full">
                    <option value="">Toutes les dates</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="tomorrow">Demain</option>
                    <option value="week">Cette semaine</option>
                </select>

                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="input input-bordered w-full" />
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Date & Heure</th>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                    <tr class="hover">
                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_datetime)->format('d/m/Y H:i') }}</td>
                        <td class="font-medium">{{ $appointment->patient->name }}<br>
                            <span class="text-xs text-base-content/60">{{ $appointment->patient->medical_record_number }}</span>
                        </td>
                        <td>
                            @php
                                $serviceLabels = [
                                    'toxicologie' => 'Toxicologie',
                                    'psychopathologie' => 'Psychopathologie',
                                    'medecine' => 'Médecine',
                                ];
                            @endphp
                            <span class="badge badge-info badge-sm">{{ $serviceLabels[$appointment->service_type] }}</span>
                        </td>
                        <td>{{ Str::limit($appointment->reason, 40) ?? '-' }}</td>
                        <td>
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
                            <span class="badge {{ $statusClasses[$appointment->status] }} badge-sm">{{ $statusLabels[$appointment->status] }}</span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                @if($appointment->status === 'scheduled')
                                    <button wire:click="updateStatus({{ $appointment->id }}, 'completed')" class="btn btn-xs btn-success" title="Marquer effectué">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button wire:click="updateStatus({{ $appointment->id }}, 'cancelled')" class="btn btn-xs btn-error" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                                <button wire:click="edit({{ $appointment->id }})" class="btn btn-xs btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $appointment->id }})" wire:confirm="Supprimer ce rendez-vous ?" class="btn btn-xs btn-ghost text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover">
                        <td colspan="6" class="text-center py-8">Aucun rendez-vous trouvé</td>
                     </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-actions justify-end p-4">
            {{ $appointments->links() }}
        </div>
    </div>

    {{-- Modal Formulaire --}}
    <dialog class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="font-bold text-lg mb-4">{{ $editingId ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous' }}</h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Patient *</span></label>
                        <select wire:model="patientId" class="select select-bordered" required>
                            <option value="">Sélectionner un patient</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->medical_record_number }}</option>
                            @endforeach
                        </select>
                        @error('patientId') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Service *</span></label>
                        <select wire:model="service_type" class="select select-bordered" required>
                            <option value="">Sélectionner</option>
                            @foreach($serviceTypes as $service)
                                <option value="{{ $service['id'] }}">{{ $service['name'] }}</option>
                            @endforeach
                        </select>
                        @error('service_type') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-medium">Date et heure *</span></label>
                        <input type="datetime-local" wire:model="appointment_datetime" class="input input-bordered" required />
                        @error('appointment_datetime') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-medium">Motif</span></label>
                        <textarea wire:model="reason" class="textarea textarea-bordered" rows="2" placeholder="Raison de la consultation"></textarea>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-medium">Notes</span></label>
                        <textarea wire:model="notes" class="textarea textarea-bordered" rows="2" placeholder="Informations complémentaires"></textarea>
                    </div>
                </div>

                <div class="modal-action mt-6">
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
