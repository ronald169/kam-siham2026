<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Medecine;
use App\Models\Patient;

new
#[Title('Consultations - Médecine Générale')]
class extends Component {
    use WithPagination, WithFileUploads;

    public string $search = '';
    public ?int $patient_id = null;
    public array $sortBy = ['column' => 'consultation_date', 'direction' => 'desc'];

    // Formulaire
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $patientId = null;
    public string $consultation_date = '';
    public string $consultation_reason = '';
    public string $illness_history = '';
    public string $medical_history = '';
    public string $physical_examination = '';
    public string $diagnostic_hypothesis = '';
    public string $requested_exams = '';
    public string $prescribed_treatments = '';
    public string $medical_prescriptions = '';
    public ?string $next_appointment = null;
    public string $follow_up_instructions = '';
    public $documents = [];

    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    public function getConsultationsProperty()
    {
        return Medecine::query()
            ->with(['patient', 'doctor'])
            ->when(auth()->user()->role === 'medecin', fn($q) => $q->where('doctor_id', auth()->id()))
            ->when($this->patient_id, fn($q) => $q->where('patient_id', $this->patient_id))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);
    }

    public function create()
    {
        $this->resetForm();
        $this->consultation_date = date('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $consultation = Medecine::findOrFail($id);
        $this->editingId = $id;
        $this->patientId = $consultation->patient_id;
        $this->consultation_date = $consultation->consultation_date;
        $this->consultation_reason = $consultation->consultation_reason;
        $this->illness_history = $consultation->illness_history;
        $this->medical_history = $consultation->medical_history;
        $this->physical_examination = $consultation->physical_examination;
        $this->diagnostic_hypothesis = $consultation->diagnostic_hypothesis;
        $this->requested_exams = $consultation->requested_exams;
        $this->prescribed_treatments = $consultation->prescribed_treatments;
        $this->medical_prescriptions = $consultation->medical_prescriptions;
        $this->next_appointment = $consultation->next_appointment;
        $this->follow_up_instructions = $consultation->follow_up_instructions;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'consultation_reason' => 'nullable|string',
            'diagnostic_hypothesis' => 'nullable|string',
            'prescribed_treatments' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'consultation_reason' => $this->consultation_reason,
            'illness_history' => $this->illness_history,
            'medical_history' => $this->medical_history,
            'physical_examination' => $this->physical_examination,
            'diagnostic_hypothesis' => $this->diagnostic_hypothesis,
            'requested_exams' => $this->requested_exams,
            'prescribed_treatments' => $this->prescribed_treatments,
            'medical_prescriptions' => $this->medical_prescriptions,
            'next_appointment' => $this->next_appointment,
            'follow_up_instructions' => $this->follow_up_instructions,
            'status' => 'completed',
        ];

        if ($this->editingId) {
            $consultation = Medecine::findOrFail($this->editingId);
            $consultation->update($data);
            session()->flash('success', 'Consultation modifiée avec succès.');
        } else {
            $consultation = Medecine::create($data);
            session()->flash('success', 'Consultation créée avec succès.');
        }

        // Gestion des fichiers
        if (!empty($this->documents)) {
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$this->patientId}/medecine", 'public');
                $documents = $consultation->documents ?? [];
                $documents[] = ['path' => $path, 'original_name' => $file->getClientOriginalName()];
                $consultation->documents = $documents;
                $consultation->save();
            }
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Medecine::findOrFail($id)->delete();
        session()->flash('success', 'Consultation supprimée avec succès.');
    }

    public function resetForm()
    {
        $this->reset([
            'editingId', 'patientId', 'consultation_date',
            'consultation_reason', 'illness_history', 'medical_history',
            'physical_examination', 'diagnostic_hypothesis', 'requested_exams',
            'prescribed_treatments', 'medical_prescriptions', 'next_appointment',
            'follow_up_instructions', 'documents'
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'patients' => $this->patients,
            'consultations' => $this->consultations,
        ]);
    }
};

?>

<div>
    {{-- En-tête --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Consultations Médecine Générale</h1>
            <p class="text-base-content/70 mt-1">Consultations médicales généralistes</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle consultation
        </button>
    </div>

    {{-- Filtres --}}
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <select wire:model.live="patient_id" class="select select-bordered w-full">
                    <option value="">Tous les patients</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                    @endforeach
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
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Motif</th>
                        <th>Diagnostic</th>
                        <th>Médecin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                    <tr class="hover">
                        <td>{{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</td>
                        <td class="font-medium">{{ $consultation->patient->name }}</td>
                        <td>{{ Str::limit($consultation->consultation_reason, 40) ?? '-' }}</td>
                        <td>{{ Str::limit($consultation->diagnostic_hypothesis, 40) ?? '-' }}</td>
                        <td>{{ $consultation->doctor->name }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $consultation->id }})" class="btn btn-sm btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $consultation->id }})" wire:confirm="Supprimer cette consultation ?" class="btn btn-sm btn-ghost text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8">Aucune consultation trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-actions justify-end p-4">
            {{ $consultations->links() }}
        </div>
    </div>

    {{-- Modal Formulaire --}}
    <dialog class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-4xl">
            <h3 class="font-bold text-lg mb-4">{{ $editingId ? 'Modifier la consultation' : 'Nouvelle consultation' }}</h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
                        <label class="label"><span class="label-text font-medium">Date de consultation *</span></label>
                        <input type="date" wire:model="consultation_date" class="input input-bordered" required />
                        @error('consultation_date') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Sections --}}
                <div class="tabs tabs-boxed mb-4">
                    <a class="tab tab-active" onclick="event.preventDefault(); showTab('tab-clinical')">Clinique</a>
                    <a class="tab" onclick="event.preventDefault(); showTab('tab-treatment')">Traitement</a>
                </div>

                <div id="tab-clinical" class="space-y-4">
                    <textarea wire:model="consultation_reason" class="textarea textarea-bordered w-full" rows="2" placeholder="Motif de consultation"></textarea>
                    <textarea wire:model="illness_history" class="textarea textarea-bordered w-full" rows="3" placeholder="Histoire de la maladie"></textarea>
                    <textarea wire:model="medical_history" class="textarea textarea-bordered w-full" rows="2" placeholder="Antécédents médicaux"></textarea>
                    <textarea wire:model="physical_examination" class="textarea textarea-bordered w-full" rows="3" placeholder="Examen physique"></textarea>
                    <textarea wire:model="diagnostic_hypothesis" class="textarea textarea-bordered w-full" rows="2" placeholder="Hypothèse diagnostique"></textarea>
                </div>

                <div id="tab-treatment" class="space-y-4 hidden">
                    <textarea wire:model="requested_exams" class="textarea textarea-bordered w-full" rows="2" placeholder="Examens demandés"></textarea>
                    <textarea wire:model="prescribed_treatments" class="textarea textarea-bordered w-full" rows="3" placeholder="Traitements prescrits"></textarea>
                    <textarea wire:model="medical_prescriptions" class="textarea textarea-bordered w-full" rows="3" placeholder="Ordonnance"></textarea>
                    <input type="date" wire:model="next_appointment" class="input input-bordered w-full" placeholder="Prochain rendez-vous" />
                    <textarea wire:model="follow_up_instructions" class="textarea textarea-bordered w-full" rows="2" placeholder="Instructions de suivi"></textarea>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Documents (PDF, Images)</span></label>
                        <input type="file" wire:model="documents" multiple class="file-input file-input-bordered w-full" />
                        @error('documents.*') <span class="text-error text-xs">{{ $message }}</span> @enderror
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

<script>
    function showTab(tabId) {
        document.querySelectorAll('#tab-clinical, #tab-treatment').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.getElementById(tabId).classList.remove('hidden');
        document.querySelectorAll('.tabs a').forEach(t => t.classList.remove('tab-active'));
        event.target.classList.add('tab-active');
    }
</script>
