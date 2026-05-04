<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Psychopathology;
use App\Models\Patient;

new
#[Title('Modifier consultation - Psychopathologie')]
class extends Component {
    use WithFileUploads, Toast;

    public string $activeTab = 'anamnese';
    public Psychopathology $consultation;

    public ?int $patient_id = null;
    public string $consultation_date = '';

    // === ANAMNÈSE ===
    public ?string $chief_complaint = '';
    public ?string $illness_history = '';

    // === EXAMEN PSYCHIATRIQUE - PRÉSENTATION ===
    public ?string $appearance = '';
    public ?string $facial_expressions = '';
    public ?string $gait = '';
    public ?string $eye_contact = '';
    public ?string $other_presentation = '';
    public ?string $contact_quality = '';

    // === COMPORTEMENT ===
    public bool $agitation = false;
    public bool $impulses = false;
    public bool $stupor = false;
    public bool $catalepsy = false;
    public bool $tics = false;
    public ?string $other_behaviors = '';

    // === SOMMEIL ===
    public bool $insomnia = false;
    public bool $daytime_sleepiness = false;
    public bool $hypersomnia = false;
    public bool $dream_disturbances = false;
    public ?string $other_sleep_issues = '';

    // === CONDUITES ALIMENTAIRES ===
    public bool $food_restriction = false;
    public bool $food_refusal = false;
    public bool $excessive_eating = false;
    public bool $excessive_drinking = false;
    public ?string $other_eating_behaviors = '';

    // === VIE SEXUELLE ET AFFECTIVE ===
    public ?string $sexual_orientation = '';
    public ?string $sexual_activity_frequency = '';
    public bool $masturbation = false;
    public bool $impotence = false;
    public ?string $other_sexual_issues = '';

    // === TROUBLES DES CONDUITES SOCIALES ===
    public bool $suicidal_ideation = false;
    public bool $suicide_attempts = false;
    public bool $suicidal_equivalents = false;
    public bool $runaway = false;
    public bool $pathological_stealing = false;
    public bool $sexual_offenses = false;
    public ?string $other_social_conduct_disorders = '';

    // === ADDICTIONS ===
    public bool $alcoholism = false;
    public bool $smoking = false;
    public ?string $other_addictions = '';

    // === TROUBLES DU LANGAGE ===
    public ?string $speech_disorders = '';

    // === TROUBLES DE LA MÉMOIRE ===
    public ?string $memory_disorders = '';

    // === TROUBLES DE LA PENSÉE ===
    public ?string $thought_flow_disorders = '';
    public ?string $thought_content_disorders = '';
    public ?string $global_thought_distortion = '';

    // === JUGEMENT ===
    public ?string $judgment_disorders = '';

    // === PERCEPTION ===
    public ?string $hallucinations = '';

    // === CONSCIENCE ET VIGILANCE ===
    public ?string $attention_quality = '';
    public ?string $spatiotemporal_orientation = '';
    public bool $hypovigilance_hypervigilance = false;
    public bool $twilight_states = false;
    public bool $oniric_states = false;
    public ?string $other_consciousness_disorders = '';

    // === AFFECTS ET HUMEUR ===
    public ?string $affect_expression_disorders = '';
    public ?string $mood_disorders = '';

    // === EXAMEN PHYSIQUE ===
    public ?string $vital_signs = '';
    public ?string $general_condition = '';
    public ?string $cardiovascular_exam = '';
    public ?string $pulmonary_exam = '';
    public ?string $neurological_exam = '';

    // === CONCLUSION ===
    public ?string $clinical_conclusion = '';
    public ?string $diagnostic_discussion = '';
    public ?string $psychological_assesment_summary = '';
    public ?string $treatment_recommendations = '';

    // Documents
    public $documents = [];
    public array $existingDocuments = [];

    public function mount($id)
    {
        $this->consultation = Psychopathology::with('patient')->findOrFail($id);
        $this->loadConsultationData();
    }

    public function loadConsultationData()
    {
        $this->patient_id = $this->consultation->patient_id;
        $this->consultation_date = $this->consultation->consultation_date;

        // Anamnèse
        $this->chief_complaint = $this->consultation->chief_complaint;
        $this->illness_history = $this->consultation->illness_history;

        // Présentation
        $this->appearance = $this->consultation->appearance;
        $this->facial_expressions = $this->consultation->facial_expressions;
        $this->gait = $this->consultation->gait;
        $this->eye_contact = $this->consultation->eye_contact;
        $this->other_presentation = $this->consultation->other_presentation;
        $this->contact_quality = $this->consultation->contact_quality;

        // Comportement
        $this->agitation = $this->consultation->agitation ?? false;
        $this->impulses = $this->consultation->impulses ?? false;
        $this->stupor = $this->consultation->stupor ?? false;
        $this->catalepsy = $this->consultation->catalepsy ?? false;
        $this->tics = $this->consultation->tics ?? false;
        $this->other_behaviors = $this->consultation->other_behaviors;

        // Sommeil
        $this->insomnia = $this->consultation->insomnia ?? false;
        $this->daytime_sleepiness = $this->consultation->daytime_sleepiness ?? false;
        $this->hypersomnia = $this->consultation->hypersomnia ?? false;
        $this->dream_disturbances = $this->consultation->dream_disturbances ?? false;
        $this->other_sleep_issues = $this->consultation->other_sleep_issues;

        // Alimentation
        $this->food_restriction = $this->consultation->food_restriction ?? false;
        $this->food_refusal = $this->consultation->food_refusal ?? false;
        $this->excessive_eating = $this->consultation->excessive_eating ?? false;
        $this->excessive_drinking = $this->consultation->excessive_drinking ?? false;
        $this->other_eating_behaviors = $this->consultation->other_eating_behaviors;

        // Sexualité
        $this->sexual_orientation = $this->consultation->sexual_orientation;
        $this->sexual_activity_frequency = $this->consultation->sexual_activity_frequency;
        $this->masturbation = $this->consultation->masturbation ?? false;
        $this->impotence = $this->consultation->impotence ?? false;
        $this->other_sexual_issues = $this->consultation->other_sexual_issues;

        // Conduites sociales
        $this->suicidal_ideation = $this->consultation->suicidal_ideation ?? false;
        $this->suicide_attempts = $this->consultation->suicide_attempts ?? false;
        $this->suicidal_equivalents = $this->consultation->suicidal_equivalents ?? false;
        $this->runaway = $this->consultation->runaway ?? false;
        $this->pathological_stealing = $this->consultation->pathological_stealing ?? false;
        $this->sexual_offenses = $this->consultation->sexual_offenses ?? false;
        $this->other_social_conduct_disorders = $this->consultation->other_social_conduct_disorders;

        // Addictions
        $this->alcoholism = $this->consultation->alcoholism ?? false;
        $this->smoking = $this->consultation->smoking ?? false;
        $this->other_addictions = $this->consultation->other_addictions;

        // Cognitif
        $this->speech_disorders = $this->consultation->speech_disorders;
        $this->memory_disorders = $this->consultation->memory_disorders;
        $this->thought_flow_disorders = $this->consultation->thought_flow_disorders;
        $this->thought_content_disorders = $this->consultation->thought_content_disorders;
        $this->global_thought_distortion = $this->consultation->global_thought_distortion;
        $this->judgment_disorders = $this->consultation->judgment_disorders;
        $this->hallucinations = $this->consultation->hallucinations;

        // Conscience
        $this->attention_quality = $this->consultation->attention_quality;
        $this->spatiotemporal_orientation = $this->consultation->spatiotemporal_orientation;
        $this->hypovigilance_hypervigilance = $this->consultation->hypovigilance_hypervigilance ?? false;
        $this->twilight_states = $this->consultation->twilight_states ?? false;
        $this->oniric_states = $this->consultation->oniric_states ?? false;
        $this->other_consciousness_disorders = $this->consultation->other_consciousness_disorders;

        // Affects
        $this->affect_expression_disorders = $this->consultation->affect_expression_disorders;
        $this->mood_disorders = $this->consultation->mood_disorders;

        // Examen physique
        $this->vital_signs = $this->consultation->vital_signs;
        $this->general_condition = $this->consultation->general_condition;
        $this->cardiovascular_exam = $this->consultation->cardiovascular_exam;
        $this->pulmonary_exam = $this->consultation->pulmonary_exam;
        $this->neurological_exam = $this->consultation->neurological_exam;

        // Conclusion
        $this->clinical_conclusion = $this->consultation->clinical_conclusion;
        $this->diagnostic_discussion = $this->consultation->diagnostic_discussion;
        $this->psychological_assesment_summary = $this->consultation->psychological_assesment_summary;
        $this->treatment_recommendations = $this->consultation->treatment_recommendations;

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
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $patient = Patient::find($this->patient_id);
        $patientFolder = $patient->medical_record_number;

        $this->consultation->update([
            'patient_id' => $this->patient_id,
            'consultation_date' => $this->consultation_date,

            // Anamnèse
            'chief_complaint' => $this->chief_complaint,
            'illness_history' => $this->illness_history,

            // Présentation
            'appearance' => $this->appearance,
            'facial_expressions' => $this->facial_expressions,
            'gait' => $this->gait,
            'eye_contact' => $this->eye_contact,
            'other_presentation' => $this->other_presentation,
            'contact_quality' => $this->contact_quality,

            // Comportement
            'agitation' => $this->agitation,
            'impulses' => $this->impulses,
            'stupor' => $this->stupor,
            'catalepsy' => $this->catalepsy,
            'tics' => $this->tics,
            'other_behaviors' => $this->other_behaviors,

            // Sommeil
            'insomnia' => $this->insomnia,
            'daytime_sleepiness' => $this->daytime_sleepiness,
            'hypersomnia' => $this->hypersomnia,
            'dream_disturbances' => $this->dream_disturbances,
            'other_sleep_issues' => $this->other_sleep_issues,

            // Alimentation
            'food_restriction' => $this->food_restriction,
            'food_refusal' => $this->food_refusal,
            'excessive_eating' => $this->excessive_eating,
            'excessive_drinking' => $this->excessive_drinking,
            'other_eating_behaviors' => $this->other_eating_behaviors,

            // Sexualité
            'sexual_orientation' => $this->sexual_orientation,
            'sexual_activity_frequency' => $this->sexual_activity_frequency,
            'masturbation' => $this->masturbation,
            'impotence' => $this->impotence,
            'other_sexual_issues' => $this->other_sexual_issues,

            // Conduites sociales
            'suicidal_ideation' => $this->suicidal_ideation,
            'suicide_attempts' => $this->suicide_attempts,
            'suicidal_equivalents' => $this->suicidal_equivalents,
            'runaway' => $this->runaway,
            'pathological_stealing' => $this->pathological_stealing,
            'sexual_offenses' => $this->sexual_offenses,
            'other_social_conduct_disorders' => $this->other_social_conduct_disorders,

            // Addictions
            'alcoholism' => $this->alcoholism,
            'smoking' => $this->smoking,
            'other_addictions' => $this->other_addictions,

            // Cognitif
            'speech_disorders' => $this->speech_disorders,
            'memory_disorders' => $this->memory_disorders,
            'thought_flow_disorders' => $this->thought_flow_disorders,
            'thought_content_disorders' => $this->thought_content_disorders,
            'global_thought_distortion' => $this->global_thought_distortion,
            'judgment_disorders' => $this->judgment_disorders,
            'hallucinations' => $this->hallucinations,

            // Conscience
            'attention_quality' => $this->attention_quality,
            'spatiotemporal_orientation' => $this->spatiotemporal_orientation,
            'hypovigilance_hypervigilance' => $this->hypovigilance_hypervigilance,
            'twilight_states' => $this->twilight_states,
            'oniric_states' => $this->oniric_states,
            'other_consciousness_disorders' => $this->other_consciousness_disorders,

            // Affects
            'affect_expression_disorders' => $this->affect_expression_disorders,
            'mood_disorders' => $this->mood_disorders,

            // Examen physique
            'vital_signs' => $this->vital_signs,
            'general_condition' => $this->general_condition,
            'cardiovascular_exam' => $this->cardiovascular_exam,
            'pulmonary_exam' => $this->pulmonary_exam,
            'neurological_exam' => $this->neurological_exam,

            // Conclusion
            'clinical_conclusion' => $this->clinical_conclusion,
            'diagnostic_discussion' => $this->diagnostic_discussion,
            'psychological_assesment_summary' => $this->psychological_assesment_summary,
            'treatment_recommendations' => $this->treatment_recommendations,
        ]);

        // Nouveaux documents
        if (!empty($this->documents)) {
            $documentsArray = $this->existingDocuments;
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$patientFolder}/psychopathologie", 'public');
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

        $this->success('Consultation modifiée avec succès.', redirectTo: route('consultations.psychopathologie.show', $this->consultation->id));
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
                <a class="tab {{ $activeTab === 'anamnese' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'anamnese')">Anamnèse</a>
                <a class="tab {{ $activeTab === 'presentation' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'presentation')">Présentation</a>
                <a class="tab {{ $activeTab === 'behavior' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'behavior')">Comportement</a>
                <a class="tab {{ $activeTab === 'sleep' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'sleep')">Sommeil</a>
                <a class="tab {{ $activeTab === 'eating' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'eating')">Alimentation</a>
                <a class="tab {{ $activeTab === 'sexual' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'sexual')">Sexualité</a>
                <a class="tab {{ $activeTab === 'social' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'social')">Conduites sociales</a>
                <a class="tab {{ $activeTab === 'addictions' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'addictions')">Addictions</a>
                <a class="tab {{ $activeTab === 'cognitive' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'cognitive')">Cognitif</a>
                <a class="tab {{ $activeTab === 'consciousness' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consciousness')">Conscience</a>
                <a class="tab {{ $activeTab === 'affect' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'affect')">Affects/Humeur</a>
                <a class="tab {{ $activeTab === 'physical' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'physical')">Examen physique</a>
                <a class="tab {{ $activeTab === 'conclusion' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'conclusion')">Conclusion</a>
                <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">Documents</a>
            </div>

            {{-- Les onglets ont la même structure que dans le create --}}
            {{-- Anamnèse --}}
            <div class="{{ $activeTab === 'anamnese' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Motif principal" wire:model="chief_complaint" rows="3" />
                <x-textarea label="Histoire de la maladie" wire:model="illness_history" rows="5" />
            </div>

            {{-- Présentation --}}
            <div class="{{ $activeTab === 'presentation' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Apparence" wire:model="appearance" />
                    <x-input label="Mimique" wire:model="facial_expressions" />
                    <x-input label="Démarche" wire:model="gait" />
                    <x-input label="Regard" wire:model="eye_contact" />
                    <x-input label="Qualité du contact" wire:model="contact_quality" />
                </div>
            </div>

            {{-- Comportement --}}
            <div class="{{ $activeTab === 'behavior' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Agitation" wire:model="agitation" />
                    <x-toggle label="Impulsions" wire:model="impulses" />
                    <x-toggle label="Stupeur" wire:model="stupor" />
                    <x-toggle label="Catalepsie" wire:model="catalepsy" />
                    <x-toggle label="Tics" wire:model="tics" />
                </div>
            </div>

            {{-- Sommeil --}}
            <div class="{{ $activeTab === 'sleep' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Insomnie" wire:model="insomnia" />
                    <x-toggle label="Somnolence diurne" wire:model="daytime_sleepiness" />
                    <x-toggle label="Hypersomnie" wire:model="hypersomnia" />
                    <x-toggle label="Perturbation onirique" wire:model="dream_disturbances" />
                </div>
            </div>

            {{-- Alimentation --}}
            <div class="{{ $activeTab === 'eating' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Restriction" wire:model="food_restriction" />
                    <x-toggle label="Refus alimentaire" wire:model="food_refusal" />
                    <x-toggle label="Excès alimentaire" wire:model="excessive_eating" />
                    <x-toggle label="Excès boissons" wire:model="excessive_drinking" />
                </div>
            </div>

            {{-- Sexualité --}}
            <div class="{{ $activeTab === 'sexual' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Orientation" wire:model="sexual_orientation" />
                    <x-input label="Fréquence" wire:model="sexual_activity_frequency" />
                    <x-toggle label="Masturbation" wire:model="masturbation" />
                    <x-toggle label="Impuissance" wire:model="impotence" />
                </div>
            </div>

            {{-- Conduites sociales --}}
            <div class="{{ $activeTab === 'social' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Idées suicidaires" wire:model="suicidal_ideation" />
                    <x-toggle label="Tentatives suicide" wire:model="suicide_attempts" />
                    <x-toggle label="Fugues" wire:model="runaway" />
                    <x-toggle label="Vol" wire:model="pathological_stealing" />
                </div>
            </div>

            {{-- Addictions --}}
            <div class="{{ $activeTab === 'addictions' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Alcoolisme" wire:model="alcoholism" />
                    <x-toggle label="Tabagisme" wire:model="smoking" />
                </div>
            </div>

            {{-- Cognitif --}}
            <div class="{{ $activeTab === 'cognitive' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Troubles langage" wire:model="speech_disorders" rows="2" />
                <x-textarea label="Troubles mémoire" wire:model="memory_disorders" rows="2" />
                <x-textarea label="Troubles pensée" wire:model="thought_flow_disorders" rows="2" />
                <x-textarea label="Hallucinations" wire:model="hallucinations" rows="2" />
            </div>

            {{-- Conscience --}}
            <div class="{{ $activeTab === 'consciousness' ? '' : 'hidden' }} space-y-4">
                <x-input label="Attention" wire:model="attention_quality" />
                <x-input label="Orientation" wire:model="spatiotemporal_orientation" />
                <x-toggle label="Hypo/Hyper vigilance" wire:model="hypovigilance_hypervigilance" />
            </div>

            {{-- Affects --}}
            <div class="{{ $activeTab === 'affect' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Expression affects" wire:model="affect_expression_disorders" rows="2" />
                <x-textarea label="Troubles humeur" wire:model="mood_disorders" rows="2" />
            </div>

            {{-- Examen physique --}}
            <div class="{{ $activeTab === 'physical' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Constantes" wire:model="vital_signs" rows="2" />
                <x-input label="État général" wire:model="general_condition" />
                <x-textarea label="CV" wire:model="cardiovascular_exam" rows="2" />
                <x-textarea label="Pulmonaire" wire:model="pulmonary_exam" rows="2" />
                <x-textarea label="Neuro" wire:model="neurological_exam" rows="2" />
            </div>

            {{-- Conclusion --}}
            <div class="{{ $activeTab === 'conclusion' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Conclusion clinique" wire:model="clinical_conclusion" rows="3" />
                <x-textarea label="Discussion" wire:model="diagnostic_discussion" rows="3" />
                <x-textarea label="Bilan psychologique" wire:model="psychological_assesment_summary" rows="3" />
                <x-textarea label="Recommandations" wire:model="treatment_recommendations" rows="3" />
            </div>

            {{-- Documents --}}
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
                <x-button label="Annuler" link="{{ route('consultations.psychopathologie.show', $consultation->id) }}" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="update" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
