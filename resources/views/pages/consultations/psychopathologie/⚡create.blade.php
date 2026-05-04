<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Psychopathology;
use App\Models\Patient;

new
#[Title('Nouvelle consultation - Psychopathologie')]
class extends Component {
    use WithFileUploads, Toast;

    public string $activeTab = 'anamnese';
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

    public function getPatientsProperty()
    {
        return Patient::orderBy('name')->get(['id', 'name', 'medical_record_number']);
    }

    public function save()
    {
        $this->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $patient = Patient::find($this->patient_id);
        $patientFolder = $patient->medical_record_number;

        $consultation = Psychopathology::create([
            'patient_id' => $this->patient_id,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'status' => 'completed',

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

        // Gestion des fichiers
        if (!empty($this->documents)) {
            $documentsArray = [];
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
            $consultation->documents = $documentsArray;
            $consultation->save();
        }

        $this->success('Consultation créée avec succès.', redirectTo: route('consultations.psychopathologie.show', $consultation->id));
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
        <h1 class="text-3xl font-bold">Nouvelle consultation - Psychopathologie</h1>
        <p class="text-base-content/70 mt-1">Créer une évaluation psychiatrique complète</p>
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

            {{-- Onglets principaux --}}
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

            {{-- ==================== ONGLET ANAMNÈSE ==================== --}}
            <div class="{{ $activeTab === 'anamnese' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Motif principal" wire:model="chief_complaint" rows="3" placeholder="Raison de la consultation, plainte principale..." />
                <x-textarea label="Histoire de la maladie" wire:model="illness_history" rows="5" placeholder="Évolution, antécédents, traitements antérieurs..." />
            </div>

            {{-- ==================== ONGLET PRÉSENTATION ==================== --}}
            <div class="{{ $activeTab === 'presentation' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Apparence / Tenue" wire:model="appearance" placeholder="Tenue, hygiène..." />
                    <x-input label="Mimique" wire:model="facial_expressions" placeholder="Expressions faciales..." />
                    <x-input label="Démarche" wire:model="gait" placeholder="Façon de marcher..." />
                    <x-input label="Regard" wire:model="eye_contact" placeholder="Fuyant, soutenu..." />
                    <x-input label="Qualité du contact" wire:model="contact_quality" placeholder="Bon, difficile, hostile..." />
                    <x-textarea label="Autres" wire:model="other_presentation" rows="2" placeholder="Autres éléments de présentation..." />
                </div>
            </div>

            {{-- ==================== ONGLET COMPORTEMENT ==================== --}}
            <div class="{{ $activeTab === 'behavior' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Agitation" wire:model="agitation" />
                    <x-toggle label="Impulsions" wire:model="impulses" />
                    <x-toggle label="Stupeur" wire:model="stupor" />
                    <x-toggle label="Catalepsie" wire:model="catalepsy" />
                    <x-toggle label="Tics" wire:model="tics" />
                </div>
                <x-textarea label="Autres comportements" wire:model="other_behaviors" rows="2" placeholder="Autres comportements observés..." />
            </div>

            {{-- ==================== ONGLET SOMMEIL ==================== --}}
            <div class="{{ $activeTab === 'sleep' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Insomnie" wire:model="insomnia" />
                    <x-toggle label="Somnolence diurne" wire:model="daytime_sleepiness" />
                    <x-toggle label="Hypersomnie" wire:model="hypersomnia" />
                    <x-toggle label="Perturbation onirique" wire:model="dream_disturbances" />
                </div>
                <x-textarea label="Autres troubles du sommeil" wire:model="other_sleep_issues" rows="2" />
            </div>

            {{-- ==================== ONGLET ALIMENTATION ==================== --}}
            <div class="{{ $activeTab === 'eating' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Restriction alimentaire" wire:model="food_restriction" />
                    <x-toggle label="Refus alimentaire" wire:model="food_refusal" />
                    <x-toggle label="Excès alimentaire" wire:model="excessive_eating" />
                    <x-toggle label="Excès de boissons" wire:model="excessive_drinking" />
                </div>
                <x-textarea label="Autres conduites alimentaires" wire:model="other_eating_behaviors" rows="2" />
            </div>

            {{-- ==================== ONGLET SEXUALITÉ ==================== --}}
            <div class="{{ $activeTab === 'sexual' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Orientation sexuelle" wire:model="sexual_orientation" placeholder="Hétérosexuelle, homosexuelle, bisexuelle..." />
                    <x-input label="Fréquence" wire:model="sexual_activity_frequency" placeholder="Régulière, occasionnelle, absente..." />
                    <x-toggle label="Masturbation" wire:model="masturbation" />
                    <x-toggle label="Impuissance" wire:model="impotence" />
                </div>
                <x-textarea label="Autres" wire:model="other_sexual_issues" rows="2" />
            </div>

            {{-- ==================== ONGLET CONDUITES SOCIALES ==================== --}}
            <div class="{{ $activeTab === 'social' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Idées suicidaires" wire:model="suicidal_ideation" />
                    <x-toggle label="Tentatives suicide" wire:model="suicide_attempts" />
                    <x-toggle label="Équivalents suicidaires" wire:model="suicidal_equivalents" />
                    <x-toggle label="Fugues" wire:model="runaway" />
                    <x-toggle label="Vol pathologique" wire:model="pathological_stealing" />
                    <x-toggle label="Attentat aux mœurs" wire:model="sexual_offenses" />
                </div>
                <x-textarea label="Autres" wire:model="other_social_conduct_disorders" rows="2" />
            </div>

            {{-- ==================== ONGLET ADDICTIONS ==================== --}}
            <div class="{{ $activeTab === 'addictions' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-toggle label="Alcoolisme" wire:model="alcoholism" />
                    <x-toggle label="Tabagisme" wire:model="smoking" />
                </div>
                <x-textarea label="Autres addictions" wire:model="other_addictions" rows="2" placeholder="Cannabis, cocaïne, médicaments..." />
            </div>

            {{-- ==================== ONGLET COGNITIF ==================== --}}
            <div class="{{ $activeTab === 'cognitive' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Troubles du langage" wire:model="speech_disorders" rows="2" />
                <x-textarea label="Troubles de la mémoire" wire:model="memory_disorders" rows="2" />
                <x-textarea label="Troubles du cours de la pensée" wire:model="thought_flow_disorders" rows="2" />
                <x-textarea label="Troubles du contenu de la pensée" wire:model="thought_content_disorders" rows="2" />
                <x-textarea label="Distorsion globale de la pensée" wire:model="global_thought_distortion" rows="2" />
                <x-textarea label="Troubles du jugement" wire:model="judgment_disorders" rows="2" />
                <x-textarea label="Hallucinations" wire:model="hallucinations" rows="2" placeholder="Auditives, visuelles, tactiles..." />
            </div>

            {{-- ==================== ONGLET CONSCIENCE ==================== --}}
            <div class="{{ $activeTab === 'consciousness' ? '' : 'hidden' }} space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Attention" wire:model="attention_quality" placeholder="Normale, diminuée..." />
                    <x-input label="Orientation temporo-spatiale" wire:model="spatiotemporal_orientation" placeholder="Orienté, désorienté..." />
                    <x-toggle label="Hypo/Hyper vigilance" wire:model="hypovigilance_hypervigilance" />
                    <x-toggle label="États crépusculaires" wire:model="twilight_states" />
                    <x-toggle label="États oniroïdes" wire:model="oniric_states" />
                </div>
                <x-textarea label="Autres" wire:model="other_consciousness_disorders" rows="2" />
            </div>

            {{-- ==================== ONGLET AFFECTS ET HUMEUR ==================== --}}
            <div class="{{ $activeTab === 'affect' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Troubles de l'expression des affects" wire:model="affect_expression_disorders" rows="2" />
                <x-textarea label="Troubles de l'humeur" wire:model="mood_disorders" rows="2" />
            </div>

            {{-- ==================== ONGLET EXAMEN PHYSIQUE ==================== --}}
            <div class="{{ $activeTab === 'physical' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Constantes" wire:model="vital_signs" rows="2" placeholder="TA, FC, FR, T°..." />
                <x-input label="État général" wire:model="general_condition" />
                <x-textarea label="Examen cardiovasculaire" wire:model="cardiovascular_exam" rows="2" />
                <x-textarea label="Examen pulmonaire" wire:model="pulmonary_exam" rows="2" />
                <x-textarea label="Examen neurologique" wire:model="neurological_exam" rows="2" />
            </div>

            {{-- ==================== ONGLET CONCLUSION ==================== --}}
            <div class="{{ $activeTab === 'conclusion' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Conclusion clinique" wire:model="clinical_conclusion" rows="3" />
                <x-textarea label="Discussion diagnostique" wire:model="diagnostic_discussion" rows="3" />
                <x-textarea label="Bilan psychologique" wire:model="psychological_assesment_summary" rows="3" />
                <x-textarea label="Recommandations thérapeutiques" wire:model="treatment_recommendations" rows="3" />
            </div>

            {{-- ==================== ONGLET DOCUMENTS ==================== --}}
            <div class="{{ $activeTab === 'documents' ? '' : 'hidden' }} space-y-4">
                <x-file
                    label="Documents (examens, résultats)"
                    wire:model="documents"
                    accept="pdf,jpg,jpeg,png,doc,docx"
                    multiple
                    hint="PDF, Images, Documents (Max 10MB)" />
            </div>

            <x-slot:actions>
                <x-button label="Annuler" link="{{ route('consultations.psychopathologie.index') }}" />
                <x-button label="Créer la consultation" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
