<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Medecine;
use App\Models\Patient;

new
#[Title('Modifier consultation - Médecine Générale')]
class extends Component {
    use WithFileUploads, Toast;

    public string $activeTab = 'consultation';
    public Medecine $consultation;

    public ?int $patient_id = null;
    public string $consultation_date = '';

    // === CONSULTATION ===
    public ?string $consultation_reason = '';
    public ?string $illness_history = '';
    public ?string $medical_history = '';
    public ?string $physical_examination = '';
    public ?string $diagnostic_hypothesis = '';

    // === EXAMENS ===
    public ?string $requested_exams = '';

    // === TRAITEMENT ===
    public ?string $prescribed_treatments = '';
    public ?string $medical_prescriptions = '';

    // === SUIVI ===
    public ?string $next_appointment = null;
    public ?string $follow_up_instructions = '';

    // Documents
    public $documents = [];
    public array $existingDocuments = [];

    public function mount($id)
    {
        $this->consultation = Medecine::with('patient')->findOrFail($id);
        $this->loadConsultationData();
    }

    public function loadConsultationData()
    {
        $this->patient_id = $this->consultation->patient_id;
        $this->consultation_date = $this->consultation->consultation_date;
        $this->consultation_reason = $this->consultation->consultation_reason;
        $this->illness_history = $this->consultation->illness_history;
        $this->medical_history = $this->consultation->medical_history;
        $this->physical_examination = $this->consultation->physical_examination;
        $this->diagnostic_hypothesis = $this->consultation->diagnostic_hypothesis;
        $this->requested_exams = $this->consultation->requested_exams;
        $this->prescribed_treatments = $this->consultation->prescribed_treatments;
        $this->medical_prescriptions = $this->consultation->medical_prescriptions;
        $this->next_appointment = $this->consultation->next_appointment;
        $this->follow_up_instructions = $this->consultation->follow_up_instructions;
        $this->existingDocuments = $this->consultation->documents ?? [];
    }

    public function getPatientsProperty()
    {
        return Patient::orderBy('name')->get(['id', 'name', 'medical_record_number']);
    }

    public function deleteDocument($index)
    {
        if (isset($this->existingDocuments[$index])) {
            \Storage::disk('public')->delete($this->existingDocuments[$index]['path']);
            array_splice($this->existingDocuments, $index, 1);
            $this->success('Document supprimé.');
        }
    }

    public function downloadDocument($index)
    {
        if (isset($this->existingDocuments[$index])) {
            return response()->download(
                storage_path('app/public/' . $this->existingDocuments[$index]['path']),
                $this->existingDocuments[$index]['original_name']
            );
        }
    }

    public function update()
    {
        $this->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'consultation_reason' => 'nullable|string',
            'diagnostic_hypothesis' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $patient = Patient::find($this->patient_id);
        $patientFolder = $patient->medical_record_number;

        $this->consultation->update([
            'patient_id' => $this->patient_id,
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
        ]);

        // Nouveaux documents
        if (!empty($this->documents)) {
            $documentsArray = $this->existingDocuments;
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$patientFolder}/medecine", 'public');
                $documentsArray[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString()
                ];
            }
            $this->consultation->documents = $documentsArray;
            $this->consultation->save();
        }

        $this->success('Consultation modifiée avec succès.', redirectTo: route('consultations.medecine.show', $this->consultation->id));
    }

    public function render()
    {
        return $this->view([
            'patients' => $this->patients,
        ]);
    }
};

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Modifier la consultation</h1>
        <p class="text-base-content/70 mt-1">Patient: {{ $consultation->patient->name }} - Date: {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</p>
    </div>

    <x-card>
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <x-choices-offline label="Patient" wire:model="patient_id" :options="$patients" option-value="id" option-label="name" required id="patient_id" name="patient_id" single clearable searchable />
                <x-datepicker label="Date" wire:model="consultation_date" required id="consultation_date" name="consultation_date" />
            </div>

            <div class="tabs tabs-boxed mb-4 overflow-x-auto flex-nowrap">
                <a class="tab {{ $activeTab === 'consultation' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consultation')">Consultation</a>
                <a class="tab {{ $activeTab === 'exams' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'exams')">Examens</a>
                <a class="tab {{ $activeTab === 'treatment' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatment')">Traitement</a>
                <a class="tab {{ $activeTab === 'followup' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'followup')">Suivi</a>
                <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">Documents</a>
            </div>

            <div class="{{ $activeTab === 'consultation' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Motif" wire:model="consultation_reason" rows="3" />
                <x-textarea label="Histoire maladie" wire:model="illness_history" rows="4" />
                <x-textarea label="Antécédents" wire:model="medical_history" rows="3" />
                <x-textarea label="Examen physique" wire:model="physical_examination" rows="4" />
                <x-textarea label="Diagnostic" wire:model="diagnostic_hypothesis" rows="3" />
            </div>

            <div class="{{ $activeTab === 'exams' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Examens demandés" wire:model="requested_exams" rows="3" />
            </div>

            <div class="{{ $activeTab === 'treatment' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Traitements" wire:model="prescribed_treatments" rows="3" />
                <x-textarea label="Ordonnance" wire:model="medical_prescriptions" rows="4" />
            </div>

            <div class="{{ $activeTab === 'followup' ? '' : 'hidden' }} space-y-4">
                <x-datepicker label="Prochain RDV" wire:model="next_appointment" />
                <x-textarea label="Instructions" wire:model="follow_up_instructions" rows="3" />
            </div>

            <div class="{{ $activeTab === 'documents' ? '' : 'hidden' }} space-y-4">
                @if(count($existingDocuments) > 0)
                    @foreach($existingDocuments as $index => $doc)
                        <div class="flex justify-between items-center p-2 bg-base-200 rounded">
                            <span>{{ $doc['original_name'] }}</span>
                            <div class="flex gap-1">
                                <x-button icon="o-arrow-down-tray" class="btn-xs btn-ghost" wire:click="downloadDocument({{ $index }})" />
                                <x-button icon="o-trash" class="btn-xs btn-ghost text-error" wire:click="deleteDocument({{ $index }})" wire:confirm="Supprimer ?" />
                            </div>
                        </div>
                    @endforeach
                @endif
                <x-file label="Ajouter des documents" wire:model="documents" multiple accept="pdf,jpg,jpeg,png" />
            </div>

            <x-slot:actions>
                <x-button label="Annuler" link="{{ route('consultations.medecine.show', $consultation->id) }}" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="update" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
