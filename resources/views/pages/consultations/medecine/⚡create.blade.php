<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Medecine;
use App\Models\Patient;

new
#[Title('Nouvelle consultation - Médecine Générale')]
class extends Component {
    use WithFileUploads, Toast;

    public string $activeTab = 'consultation';

    // Patient
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
    public $exam_results = [];
    public array $existingExamResults = [];

    // === TRAITEMENT ===
    public ?string $prescribed_treatments = '';
    public ?string $medical_prescriptions = '';

    // === SUIVI ===
    public ?string $next_appointment = null;
    public ?string $follow_up_instructions = '';

    // Documents
    public $documents = [];

    public function getPatientsProperty()
    {
        return Patient::orderBy('name')->get(['id', 'name', 'medical_record_number']);
    }

    public function save()
    {
        $this->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'consultation_reason' => 'nullable|string',
            'diagnostic_hypothesis' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $patient = Patient::find($this->patient_id);
        $patientFolder = $patient->medical_record_number;

        $consultation = Medecine::create([
            'patient_id' => $this->patient_id,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'status' => 'completed',

            // Consultation
            'consultation_reason' => $this->consultation_reason,
            'illness_history' => $this->illness_history,
            'medical_history' => $this->medical_history,
            'physical_examination' => $this->physical_examination,
            'diagnostic_hypothesis' => $this->diagnostic_hypothesis,

            // Examens
            'requested_exams' => $this->requested_exams,

            // Traitement
            'prescribed_treatments' => $this->prescribed_treatments,
            'medical_prescriptions' => $this->medical_prescriptions,

            // Suivi
            'next_appointment' => $this->next_appointment,
            'follow_up_instructions' => $this->follow_up_instructions,
        ]);

        // Gestion des documents
        if (!empty($this->documents)) {
            $documentsArray = [];
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
            $consultation->documents = $documentsArray;
            $consultation->save();
        }

        $this->success('Consultation créée avec succès.', redirectTo: route('consultations.medecine.show', $consultation->id));
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
        <h1 class="text-3xl font-bold">Nouvelle consultation - Médecine Générale</h1>
        <p class="text-base-content/70 mt-1">Créer une consultation médicale généraliste</p>
    </div>

    <x-card>
        <x-form wire:submit="save">
            {{-- Informations générales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <x-choices-offline
                    label="Patient"
                    wire:model="patient_id"
                    :options="$patients"
                    option-value="id"
                    option-label="name"
                    required
                    id="patient_id"
                    name="patient_id"
                    single
                    clearable
                    searchable />

                <x-datepicker
                    label="Date de consultation"
                    wire:model="consultation_date"
                    required
                    id="consultation_date"
                    name="consultation_date" />
            </div>

            {{-- Onglets --}}
            <div class="tabs tabs-boxed mb-4 overflow-x-auto flex-nowrap">
                <a class="tab {{ $activeTab === 'consultation' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consultation')">
                    <x-icon name="o-clipboard-document-list" class="h-5 w-5 mr-2" /> Consultation
                </a>
                <a class="tab {{ $activeTab === 'exams' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'exams')">
                    <x-icon name="o-document-magnifying-glass" class="h-5 w-5 mr-2" /> Examens
                </a>
                <a class="tab {{ $activeTab === 'treatment' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatment')">
                    <x-icon name="o-beaker" class="h-5 w-5 mr-2" /> Traitement
                </a>
                <a class="tab {{ $activeTab === 'followup' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'followup')">
                    <x-icon name="o-calendar" class="h-5 w-5 mr-2" /> Suivi
                </a>
                <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">
                    <x-icon name="o-paper-clip" class="h-5 w-5 mr-2" /> Documents
                </a>
            </div>

            {{-- Onglet Consultation --}}
            <div class="{{ $activeTab === 'consultation' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Motif de consultation" wire:model="consultation_reason" rows="3" placeholder="Raison de la consultation, plainte principale..." />
                <x-textarea label="Histoire de la maladie" wire:model="illness_history" rows="4" placeholder="Début, évolution, traitements antérieurs..." />
                <x-textarea label="Antécédents médicaux" wire:model="medical_history" rows="3" placeholder="ATCD médicaux, chirurgicaux, familiaux..." />
                <x-textarea label="Examen physique" wire:model="physical_examination" rows="4" placeholder="Poids, TA, FC, FR, T°, auscultation, palpation..." />
                <x-textarea label="Hypothèse diagnostique" wire:model="diagnostic_hypothesis" rows="3" placeholder="Diagnostic(s) suspecté(s)..." />
            </div>

            {{-- Onglet Examens --}}
            <div class="{{ $activeTab === 'exams' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Examens demandés" wire:model="requested_exams" rows="3" placeholder="Biologie, imagerie, examens complémentaires..." />
                <div class="alert alert-info">
                    <x-icon name="o-information-circle" class="h-5 w-5" />
                    <span>Les résultats d'examens peuvent être ajoutés dans la section Documents ci-dessous.</span>
                </div>
            </div>

            {{-- Onglet Traitement --}}
            <div class="{{ $activeTab === 'treatment' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Traitements prescrits" wire:model="prescribed_treatments" rows="3" placeholder="Médicaments, posologie, durée..." />
                <x-textarea label="Ordonnance" wire:model="medical_prescriptions" rows="4" placeholder="Détails de l'ordonnance..." />
            </div>

            {{-- Onglet Suivi --}}
            <div class="{{ $activeTab === 'followup' ? '' : 'hidden' }} space-y-4">
                <x-datepicker label="Prochain rendez-vous" wire:model="next_appointment" icon="o-calendar" />
                <x-textarea label="Instructions de suivi" wire:model="follow_up_instructions" rows="3" placeholder="Recommandations, régime, activité, surveillance..." />
            </div>

            {{-- Onglet Documents --}}
            <div class="{{ $activeTab === 'documents' ? '' : 'hidden' }} space-y-4">
                <x-file
                    label="Documents (examens, résultats)"
                    wire:model="documents"
                    accept="pdf,jpg,jpeg,png,doc,docx"
                    multiple
                    hint="PDF, Images, Documents (Max 10MB)" />
            </div>

            <x-slot:actions>
                <x-button label="Annuler" link="{{ route('consultations.medecine.index') }}" />
                <x-button label="Créer la consultation" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
