<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Toxicology;
use App\Models\Patient;

new
#[Title('Nouvelle consultation - Toxicologie')]
class extends Component {
    use WithFileUploads, Toast;

    public string $activeTab = 'history';

    // Patient
    public ?int $patient_id = null;
    public string $consultation_date = '';

    // === HISTOIRE DES CONDUITES ADDICTIVES ===
    public ?string $substances_used = '';
    public ?string $substances_start_age = '';
    public ?string $substances_start_reason = '';
    public ?string $current_consumption_motivation = '';
    public ?string $tolerance_description = '';
    public ?string $withdrawal_attempts = '';
    public ?string $stop_motivation = '';
    public ?string $substances_types = '';
    public ?string $max_abstinence_duration = '';

    // === CONSÉQUENCES ===
    public ?string $substance_relation = '';
    public bool $weight_loss = false;
    public bool $pale_complexion = false;
    public bool $withdrawal_insomnia = false;
    public bool $nightmares = false;
    public bool $hallucinations = false;
    public bool $somatic_disorders = false;
    public bool $behavioral_delirium = false;
    public bool $legal_issues = false;
    public ?string $affective_fulfillment = '';
    public ?string $sexual_fulfillment = '';

    // === ÉVALUATION ===
    public ?string $consumption_pattern = '';
    public ?string $dependency_investment = '';

    // === TABLEAU CLINIQUE ===
    public ?string $general_condition = '';
    public ?string $respiratory_signs = '';
    public ?string $neurological_signs = '';
    public ?string $psychiatric_disorders = '';
    public ?string $other_symptoms = '';

    // === ANTÉCÉDENTS ===
    public ?string $medical_surgical_history = '';
    public ?string $allergy_history = '';
    public ?string $psychiatric_history = '';
    public ?string $trauma_history = '';

    // === EXAMENS ===
    public ?string $psychological_assessment = '';
    public ?string $biological_assessment = '';

    // === CONCLUSION ET TRAITEMENT ===
    public ?string $diagnostic_conclusion = '';
    public ?string $treatment_plan = '';
    public ?string $recommendations = '';

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
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $patient = Patient::find($this->patient_id);
        $patientFolder = $patient->medical_record_number;

        $consultation = Toxicology::create([
            'patient_id' => $this->patient_id,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'status' => 'completed',

            // Histoire
            'substances_used' => $this->substances_used,
            'substances_start_age' => $this->substances_start_age,
            'substances_start_reason' => $this->substances_start_reason,
            'current_consumption_motivation' => $this->current_consumption_motivation,
            'tolerance_description' => $this->tolerance_description,
            'withdrawal_attempts' => $this->withdrawal_attempts,
            'stop_motivation' => $this->stop_motivation,
            'substances_types' => $this->substances_types,
            'max_abstinence_duration' => $this->max_abstinence_duration,

            // Conséquences
            'substance_relation' => $this->substance_relation,
            'weight_loss' => $this->weight_loss,
            'pale_complexion' => $this->pale_complexion,
            'withdrawal_insomnia' => $this->withdrawal_insomnia,
            'nightmares' => $this->nightmares,
            'hallucinations' => $this->hallucinations,
            'somatic_disorders' => $this->somatic_disorders,
            'behavioral_delirium' => $this->behavioral_delirium,
            'legal_issues' => $this->legal_issues,
            'affective_fulfillment' => $this->affective_fulfillment,
            'sexual_fulfillment' => $this->sexual_fulfillment,

            // Évaluation
            'consumption_pattern' => $this->consumption_pattern,
            'dependency_investment' => $this->dependency_investment,

            // Tableau clinique
            'general_condition' => $this->general_condition,
            'respiratory_signs' => $this->respiratory_signs,
            'neurological_signs' => $this->neurological_signs,
            'psychiatric_disorders' => $this->psychiatric_disorders,
            'other_symptoms' => $this->other_symptoms,

            // Antécédents
            'medical_surgical_history' => $this->medical_surgical_history,
            'allergy_history' => $this->allergy_history,
            'psychiatric_history' => $this->psychiatric_history,
            'trauma_history' => $this->trauma_history,

            // Examens
            'psychological_assessment' => $this->psychological_assessment,
            'biological_assessment' => $this->biological_assessment,

            // Conclusion
            'diagnostic_conclusion' => $this->diagnostic_conclusion,
            'treatment_plan' => $this->treatment_plan,
            'recommendations' => $this->recommendations,
        ]);

        // Gestion des fichiers avec nom du patient
        if (!empty($this->documents)) {
            $documentsArray = [];
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$patientFolder}/toxicologie", 'public');
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

        $this->success('Consultation créée avec succès.', redirectTo: route('consultations.toxicologie.show', $consultation->id));
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
        <h1 class="text-3xl font-bold">Nouvelle consultation - Toxicologie</h1>
        <p class="text-base-content/70 mt-1">Créer une évaluation en addictologie</p>
    </div>

    <x-card>
        <x-form wire:submit="save">
            {{-- Informations générales avec x-choices-offline --}}
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
                    placeholder="Rechercher un patient..."
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
                <a class="tab {{ $activeTab === 'history' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'history')">Histoire addictive</a>
                <a class="tab {{ $activeTab === 'consequences' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consequences')">Conséquences</a>
                <a class="tab {{ $activeTab === 'evaluation' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'evaluation')">Évaluation</a>
                <a class="tab {{ $activeTab === 'clinical' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'clinical')">Tableau clinique</a>
                <a class="tab {{ $activeTab === 'antecedents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'antecedents')">Antécédents</a>
                <a class="tab {{ $activeTab === 'exams' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'exams')">Examens</a>
                <a class="tab {{ $activeTab === 'conclusion' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'conclusion')">Conclusion & CAT</a>
                <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">Documents</a>
            </div>

            {{-- Onglet 1: Histoire addictive --}}
            <div class="{{ $activeTab === 'history' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-textarea label="Substances consommées" wire:model="substances_used" rows="2" placeholder="Ex: Cannabis, Alcool, Cocaïne..." />
                    <x-input label="Âge de début" wire:model="substances_start_age" placeholder="Ex: 15 ans" />
                    <x-textarea label="Raison du début" wire:model="substances_start_reason" rows="2" placeholder="Pourquoi a-t-il/elle commencé ?" />
                    <x-textarea label="Motivation actuelle" wire:model="current_consumption_motivation" rows="2" placeholder="Pourquoi consomme-t-il/elle actuellement ?" />
                    <x-textarea label="Description de la tolérance" wire:model="tolerance_description" rows="2" placeholder="Augmentation des doses ?" />
                    <x-textarea label="Tentatives d'arrêt" wire:model="withdrawal_attempts" rows="2" placeholder="A déjà essayé d'arrêter ?" />
                    <x-textarea label="Motivation à arrêter" wire:model="stop_motivation" rows="2" placeholder="Pourquoi veut-il/elle arrêter ?" />
                    <x-textarea label="Types de substances" wire:model="substances_types" rows="2" placeholder="Voie d'administration, fréquence..." />
                    <x-input label="Durée max d'abstinence" wire:model="max_abstinence_duration" placeholder="Ex: 2 semaines, 1 mois..." />
                </div>
            </div>

            {{-- Onglet 2: Conséquences --}}
            <div class="{{ $activeTab === 'consequences' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-input label="Relation avec la substance" wire:model="substance_relation" placeholder="Ex: Forte dépendance, usage occasionnel..." />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-toggle label="Amaigrissement" wire:model="weight_loss" />
                        <x-toggle label="Teint sombre" wire:model="pale_complexion" />
                        <x-toggle label="Insomnie de manque" wire:model="withdrawal_insomnia" />
                        <x-toggle label="Cauchemars" wire:model="nightmares" />
                        <x-toggle label="Hallucinations" wire:model="hallucinations" />
                        <x-toggle label="Troubles somatiques" wire:model="somatic_disorders" />
                        <x-toggle label="Délire/Trouble comportement" wire:model="behavioral_delirium" />
                        <x-toggle label="Problèmes judiciaires" wire:model="legal_issues" />
                    </div>
                    <x-select label="Épanouissement affectif" wire:model="affective_fulfillment" :options="[['id' => 'Bon', 'name' => 'Bon'], ['id' => 'Moyen', 'name' => 'Moyen'], ['id' => 'Mauvais', 'name' => 'Mauvais']]" option-value="id" option-label="name" placeholder="Sélectionner" id="affective_fulfillment" name="affective_fulfillment" />
                    <x-select label="Épanouissement sexuel" wire:model="sexual_fulfillment" :options="[['id' => 'Bon', 'name' => 'Bon'], ['id' => 'Moyen', 'name' => 'Moyen'], ['id' => 'Mauvais', 'name' => 'Mauvais']]" option-value="id" option-label="name" placeholder="Sélectionner" id="sexual_fulfillment" name="sexual_fulfillment" />
                </div>
            </div>

            {{-- Onglet 3: Évaluation --}}
            <div class="{{ $activeTab === 'evaluation' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-textarea label="Pattern de consommation" wire:model="consumption_pattern" rows="3" placeholder="Fréquence, quantités, contexte..." />
                    <x-textarea label="Investissement dans la dépendance" wire:model="dependency_investment" rows="3" placeholder="Temps, argent, énergie consacrés..." />
                </div>
            </div>

            {{-- Onglet 4: Tableau clinique --}}
            <div class="{{ $activeTab === 'clinical' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-select label="État général" wire:model="general_condition" :options="[['id' => 'Bon', 'name' => 'Bon'], ['id' => 'Passable', 'name' => 'Passable'], ['id' => 'Mauvais', 'name' => 'Mauvais']]" option-value="id" option-label="name" placeholder="Sélectionner" id="general_condition" name="general_condition" />
                    <x-textarea label="Signes respiratoires" wire:model="respiratory_signs" rows="2" placeholder="Toux, essoufflement..." />
                    <x-textarea label="Signes neurologiques" wire:model="neurological_signs" rows="2" placeholder="Tremblements, neuropathies..." />
                    <x-textarea label="Troubles psychiatriques" wire:model="psychiatric_disorders" rows="2" placeholder="Anxiété, dépression..." />
                    <x-textarea label="Autres symptômes" wire:model="other_symptoms" rows="2" />
                </div>
            </div>

            {{-- Onglet 5: Antécédents --}}
            <div class="{{ $activeTab === 'antecedents' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-textarea label="Antécédents médico-chirurgicaux" wire:model="medical_surgical_history" rows="2" />
                    <x-textarea label="Antécédents allergiques" wire:model="allergy_history" rows="2" />
                    <x-textarea label="Antécédents psychiatriques" wire:model="psychiatric_history" rows="2" />
                    <x-textarea label="Antécédents traumatiques" wire:model="trauma_history" rows="2" />
                </div>
            </div>

            {{-- Onglet 6: Examens --}}
            <div class="{{ $activeTab === 'exams' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-textarea label="Bilan psychologique" wire:model="psychological_assessment" rows="3" />
                    <x-textarea label="Bilan biologique" wire:model="biological_assessment" rows="3" />
                </div>
            </div>

            {{-- Onglet 7: Conclusion et CAT --}}
            <div class="{{ $activeTab === 'conclusion' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-textarea label="Conclusion diagnostique" wire:model="diagnostic_conclusion" rows="3" />
                    <x-textarea label="Plan de traitement" wire:model="treatment_plan" rows="3" />
                    <x-textarea label="Recommandations" wire:model="recommendations" rows="2" />
                </div>
            </div>

            {{-- Onglet 8: Documents --}}
            <div class="{{ $activeTab === 'documents' ? '' : 'hidden' }}">
                <div class="space-y-4">
                    <x-file
                        label="Documents (examens, résultats)"
                        wire:model="documents"
                        accept="pdf,jpg,jpeg,png,doc,docx"
                        multiple
                        hint="PDF, Images, Documents (Max 10MB)" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Annuler" link="{{ route('consultations.toxicologie.index') }}" />
                <x-button label="Créer la consultation" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
