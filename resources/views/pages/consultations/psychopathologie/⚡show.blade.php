<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Psychopathology;
use App\Models\Treatment;
use Barryvdh\DomPDF\Facade\Pdf;

new
#[Title('Fiche consultation - Psychopathologie')]
class extends Component {
    use Toast;

    public Psychopathology $consultation;
    public string $activeTab = 'info';
    public array $treatments = [];

    // Modals
    public bool $treatmentModal = false;
    public bool $deleteModal = false;
    public bool $viewTreatmentModal = false;
    public ?int $treatmentToDelete = null;
    public ?int $editingTreatmentId = null;
    public string $modalTitle = '';
    public ?array $viewingTreatment = null;

    // Formulaire traitement
    public ?int $patient_id = null;
    public string $treatment_date = '';
    public string $treatment_time = '';
    public string $observations = '';
    public string $medications_given = '';
    public string $care_provided = '';
    public ?float $cost = null;
    public string $patient_condition = '';
    public string $doctor_notes = '';
    public string $next_instructions = '';
    public string $activeFormTab = 'care';

    public function mount($id)
    {
        $this->consultation = Psychopathology::with(['patient', 'doctor'])->findOrFail($id);
        $this->loadTreatments();
    }

    public function loadTreatments()
    {
        $this->treatments = Treatment::where('treatable_type', 'App\\Models\\Psychopathology')
            ->where('treatable_id', $this->consultation->id)
            ->with('doctor')
            ->orderBy('treatment_date', 'desc')
            ->get()
            ->toArray();
    }

    public function downloadDocument($index)
    {
        $documents = $this->consultation->documents ?? [];
        if (isset($documents[$index])) {
            return response()->download(
                storage_path('app/public/' . $documents[$index]['path']),
                $documents[$index]['original_name']
            );
        }
    }

    // ==================== GESTION TRAITEMENTS ====================

    public function openCreateTreatmentModal()
    {
        $this->resetTreatmentForm();
        $this->treatment_date = date('Y-m-d');
        $this->treatment_time = date('H:i');
        $this->patient_id = $this->consultation->patient_id;
        $this->modalTitle = 'Ajouter un traitement';
        $this->editingTreatmentId = null;
        $this->treatmentModal = true;
    }

    public function openEditTreatmentModal($id)
    {
        $treatment = Treatment::findOrFail($id);
        $this->editingTreatmentId = $id;
        $this->patient_id = $treatment->patient_id;
        $this->treatment_date = $treatment->treatment_date->format('Y-m-d');
        $this->treatment_time = $treatment->treatment_time ? \Carbon\Carbon::parse($treatment->treatment_time)->format('H:i') : '';
        $this->observations = $treatment->observations;
        $this->medications_given = $treatment->medications_given;
        $this->care_provided = $treatment->care_provided;
        $this->cost = $treatment->cost;
        $this->patient_condition = $treatment->patient_condition;
        $this->doctor_notes = $treatment->doctor_notes;
        $this->next_instructions = $treatment->next_instructions;
        $this->modalTitle = 'Modifier le traitement';
        $this->treatmentModal = true;
    }

    public function viewTreatment($id)
    {
        $treatment = Treatment::with(['patient', 'doctor'])->findOrFail($id);
        $this->viewingTreatment = $treatment->toArray();
        $this->viewTreatmentModal = true;
    }

    public function saveTreatment()
    {
        $this->validate([
            'treatment_date' => 'required|date',
            'observations' => 'nullable|string',
            'patient_condition' => 'nullable|string|in:stable,amélioré,dégradé',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'treatable_type' => 'App\\Models\\Psychopathology',
            'treatable_id' => $this->consultation->id,
            'patient_id' => $this->consultation->patient_id,
            'doctor_id' => auth()->id(),
            'treatment_date' => $this->treatment_date,
            'treatment_time' => $this->treatment_time ?: null,
            'observations' => $this->observations,
            'medications_given' => $this->medications_given,
            'care_provided' => $this->care_provided,
            'cost' => $this->cost,
            'patient_condition' => $this->patient_condition,
            'doctor_notes' => $this->doctor_notes,
            'next_instructions' => $this->next_instructions,
        ];

        if ($this->editingTreatmentId) {
            $treatment = Treatment::findOrFail($this->editingTreatmentId);
            $treatment->update($data);
            $this->success('Traitement modifié avec succès.');
        } else {
            Treatment::create($data);
            $this->success('Traitement ajouté avec succès.');
        }

        $this->loadTreatments();
        $this->treatmentModal = false;
        $this->resetTreatmentForm();
    }

    public function confirmDeleteTreatment($id)
    {
        $this->treatmentToDelete = $id;
        $this->deleteModal = true;
    }

    public function deleteTreatment()
    {
        $treatment = Treatment::find($this->treatmentToDelete);
        if ($treatment) {
            $treatment->delete();
            $this->loadTreatments();
            $this->success('Traitement supprimé avec succès.');
        }
        $this->deleteModal = false;
        $this->treatmentToDelete = null;
    }

    public function resetTreatmentForm()
    {
        $this->reset([
            'editingTreatmentId', 'treatment_date', 'treatment_time', 'observations',
            'medications_given', 'care_provided', 'cost', 'patient_condition',
            'doctor_notes', 'next_instructions'
        ]);
        $this->activeFormTab = 'care';
        $this->resetValidation();
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('pdf.psychopathologie', ['consultation' => $this->consultation]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'psychopathologie_' . $this->consultation->patient->medical_record_number . '_' . $this->consultation->consultation_date . '.pdf'
        );
    }

    public function render()
    {
        return $this->view();
    }
};

?>

<div>
    {{-- ==================== EN-TÊTE ==================== --}}
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="avatar placeholder">
                <div class="bg-primary text-primary-content rounded-full w-14">
                    <span class="text-xl font-bold">{{ substr($consultation->patient->name, 0, 1) }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Consultation Psychopathologie</h1>
                <div class="flex flex-wrap gap-3 mt-1 text-sm text-base-content/70">
                    <div>👤 <a href="{{ route('patients.show', $consultation->patient->id) }}" class="link link-primary">{{ $consultation->patient->name }}</a></div>
                    <div>📋 {{ $consultation->patient->medical_record_number }}</div>
                    <div>📅 {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</div>
                    <div>👨‍⚕️ {{ $consultation->doctor->name ?? '-' }}</div>
                    <div><x-badge :value="ucfirst($consultation->status)" class="badge-info badge-sm" /></div>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <x-button label="PDF" icon="o-arrow-down-tray" class="btn-primary" wire:click="downloadPdf" spinner />
            <x-button label="Modifier" icon="o-pencil" class="btn-outline" link="{{ route('consultations.psychopathologie.edit', $consultation->id) }}" />
            <x-button label="Retour" icon="o-arrow-left" link="{{ route('consultations.psychopathologie.index') }}" />
        </div>
    </div>

    {{-- ==================== ONGLETS ==================== --}}
    <div class="tabs tabs-boxed mb-6 overflow-x-auto flex-nowrap">
        <a class="tab {{ $activeTab === 'info' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'info')">Informations</a>
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
        <a class="tab {{ $activeTab === 'treatments' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatments')">Traitements ({{ count($treatments) }})</a>
        <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">Documents</a>
    </div>

    {{-- ==================== ONGLET INFORMATIONS ==================== --}}
    <div class="{{ $activeTab === 'info' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Informations consultation" icon="o-information-circle" separator>
                <div class="space-y-2">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Date :</span><span>{{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Médecin :</span><span>{{ $consultation->doctor->name ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Statut :</span><span><x-badge :value="ucfirst($consultation->status)" class="badge-info" /></span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Créé le :</span><span>{{ $consultation->created_at ? $consultation->created_at->format('d/m/Y H:i') : '-' }}</span></div>
                </div>
            </x-card>

            <x-card title="Patient" icon="o-user" separator>
                <div class="space-y-2">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nom complet :</span><span>{{ $consultation->patient->name }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">N° Dossier :</span><span>{{ $consultation->patient->medical_record_number }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Âge :</span><span>{{ $consultation->patient->age ?? '-' }} ans</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Sexe :</span><span>{{ $consultation->patient->sex ?? '-' }}</span></div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET ANAMNÈSE ==================== --}}
    <div class="{{ $activeTab === 'anamnese' ? '' : 'hidden' }}">
        <x-card title="Anamnèse" icon="o-document-text" separator>
            <div><span class="font-medium">Motif principal :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->chief_complaint ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Histoire de la maladie :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->illness_history ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET PRÉSENTATION ==================== --}}
    <div class="{{ $activeTab === 'presentation' ? '' : 'hidden' }}">
        <x-card title="Présentation" icon="o-user" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Apparence :</span><p>{{ $consultation->appearance ?? '-' }}</p></div>
                <div><span class="font-medium">Mimique :</span><p>{{ $consultation->facial_expressions ?? '-' }}</p></div>
                <div><span class="font-medium">Démarche :</span><p>{{ $consultation->gait ?? '-' }}</p></div>
                <div><span class="font-medium">Regard :</span><p>{{ $consultation->eye_contact ?? '-' }}</p></div>
                <div><span class="font-medium">Qualité du contact :</span><p>{{ $consultation->contact_quality ?? '-' }}</p></div>
                <div><span class="font-medium">Autres :</span><p>{{ $consultation->other_presentation ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET COMPORTEMENT ==================== --}}
    <div class="{{ $activeTab === 'behavior' ? '' : 'hidden' }}">
        <x-card title="Comportement" icon="o-user-group" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Agitation :</span><x-badge :value="$consultation->agitation ? 'Oui' : 'Non'" :class="$consultation->agitation ? 'badge-error' : 'badge-success'" /></div>
                <div><span class="font-medium">Impulsions :</span><x-badge :value="$consultation->impulses ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Stupeur :</span><x-badge :value="$consultation->stupor ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Catalepsie :</span><x-badge :value="$consultation->catalepsy ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Tics :</span><x-badge :value="$consultation->tics ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres comportements :</span><p class="mt-1">{{ $consultation->other_behaviors ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET SOMMEIL ==================== --}}
    <div class="{{ $activeTab === 'sleep' ? '' : 'hidden' }}">
        <x-card title="Sommeil" icon="o-moon" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Insomnie :</span><x-badge :value="$consultation->insomnia ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Somnolence diurne :</span><x-badge :value="$consultation->daytime_sleepiness ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Hypersomnie :</span><x-badge :value="$consultation->hypersomnia ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Perturbation onirique :</span><x-badge :value="$consultation->dream_disturbances ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres troubles :</span><p class="mt-1">{{ $consultation->other_sleep_issues ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET ALIMENTATION ==================== --}}
    <div class="{{ $activeTab === 'eating' ? '' : 'hidden' }}">
        <x-card title="Conduites alimentaires" icon="o-cake" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Restriction alimentaire :</span><x-badge :value="$consultation->food_restriction ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Refus alimentaire :</span><x-badge :value="$consultation->food_refusal ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Excès alimentaire :</span><x-badge :value="$consultation->excessive_eating ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Excès de boissons :</span><x-badge :value="$consultation->excessive_drinking ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres :</span><p class="mt-1">{{ $consultation->other_eating_behaviors ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET VIE SEXUELLE ==================== --}}
    <div class="{{ $activeTab === 'sexual' ? '' : 'hidden' }}">
        <x-card title="Vie sexuelle et affective" icon="o-heart" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Orientation sexuelle :</span><span>{{ $consultation->sexual_orientation ?? '-' }}</span></div>
                <div><span class="font-medium">Fréquence :</span><span>{{ $consultation->sexual_activity_frequency ?? '-' }}</span></div>
                <div><span class="font-medium">Masturbation :</span><x-badge :value="$consultation->masturbation ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Impuissance :</span><x-badge :value="$consultation->impotence ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres :</span><p class="mt-1">{{ $consultation->other_sexual_issues ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET CONDUITES SOCIALES ==================== --}}
    <div class="{{ $activeTab === 'social' ? '' : 'hidden' }}">
        <x-card title="Troubles des conduites sociales" icon="o-user-group" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Idées suicidaires :</span><x-badge :value="$consultation->suicidal_ideation ? 'Oui' : 'Non'" class="badge-error" /></div>
                <div><span class="font-medium">Tentatives suicide :</span><x-badge :value="$consultation->suicide_attempts ? 'Oui' : 'Non'" class="badge-error" /></div>
                <div><span class="font-medium">Équivalents suicidaires :</span><x-badge :value="$consultation->suicidal_equivalents ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Fugues :</span><x-badge :value="$consultation->runaway ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Vol pathologique :</span><x-badge :value="$consultation->pathological_stealing ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Attentat aux mœurs :</span><x-badge :value="$consultation->sexual_offenses ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres :</span><p class="mt-1">{{ $consultation->other_social_conduct_disorders ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET ADDICTIONS ==================== --}}
    <div class="{{ $activeTab === 'addictions' ? '' : 'hidden' }}">
        <x-card title="Addictions" icon="o-beaker" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Alcoolisme :</span><x-badge :value="$consultation->alcoholism ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">Tabagisme :</span><x-badge :value="$consultation->smoking ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres addictions :</span><p class="mt-1">{{ $consultation->other_addictions ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET COGNITIF ==================== --}}
    <div class="{{ $activeTab === 'cognitive' ? '' : 'hidden' }}">
        <x-card title="Troubles cognitifs" icon="o-brain-circuit" separator>
            <div><span class="font-medium">Troubles du langage :</span><p>{{ $consultation->speech_disorders ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Troubles de la mémoire :</span><p>{{ $consultation->memory_disorders ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Troubles du cours de la pensée :</span><p>{{ $consultation->thought_flow_disorders ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Troubles du contenu de la pensée :</span><p>{{ $consultation->thought_content_disorders ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Distorsion globale de la pensée :</span><p>{{ $consultation->global_thought_distortion ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Troubles du jugement :</span><p>{{ $consultation->judgment_disorders ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Hallucinations :</span><p>{{ $consultation->hallucinations ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET CONSCIENCE ==================== --}}
    <div class="{{ $activeTab === 'consciousness' ? '' : 'hidden' }}">
        <x-card title="Conscience et vigilance" icon="o-eye" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Attention :</span><p>{{ $consultation->attention_quality ?? '-' }}</p></div>
                <div><span class="font-medium">Orientation temporo-spatiale :</span><p>{{ $consultation->spatiotemporal_orientation ?? '-' }}</p></div>
                <div><span class="font-medium">Hypo/Hyper vigilance :</span><x-badge :value="$consultation->hypovigilance_hypervigilance ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">États crépusculaires :</span><x-badge :value="$consultation->twilight_states ? 'Oui' : 'Non'" /></div>
                <div><span class="font-medium">États oniroïdes :</span><x-badge :value="$consultation->oniric_states ? 'Oui' : 'Non'" /></div>
                <div class="md:col-span-2"><span class="font-medium">Autres :</span><p>{{ $consultation->other_consciousness_disorders ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET AFFECTS ET HUMEUR ==================== --}}
    <div class="{{ $activeTab === 'affect' ? '' : 'hidden' }}">
        <x-card title="Affects et Humeur" icon="o-face-smile" separator>
            <div><span class="font-medium">Troubles de l'expression des affects :</span><p>{{ $consultation->affect_expression_disorders ?? '-' }}</p></div>
            <div class="mt-3"><span class="font-medium">Troubles de l'humeur :</span><p>{{ $consultation->mood_disorders ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET EXAMEN PHYSIQUE ==================== --}}
    <div class="{{ $activeTab === 'physical' ? '' : 'hidden' }}">
        <x-card title="Examen physique" icon="o-stethoscope" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Constantes :</span><p>{{ $consultation->vital_signs ?? '-' }}</p></div>
                <div><span class="font-medium">État général :</span><p>{{ $consultation->general_condition ?? '-' }}</p></div>
                <div><span class="font-medium">Examen cardiovasculaire :</span><p>{{ $consultation->cardiovascular_exam ?? '-' }}</p></div>
                <div><span class="font-medium">Examen pulmonaire :</span><p>{{ $consultation->pulmonary_exam ?? '-' }}</p></div>
                <div class="md:col-span-2"><span class="font-medium">Examen neurologique :</span><p>{{ $consultation->neurological_exam ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET CONCLUSION ==================== --}}
    <div class="{{ $activeTab === 'conclusion' ? '' : 'hidden' }}">
        <x-card title="Conclusion" icon="o-document-check" separator>
            <div><span class="font-medium">Conclusion clinique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->clinical_conclusion ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Discussion diagnostique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->diagnostic_discussion ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Bilan psychologique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->psychological_assesment_summary ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Recommandations thérapeutiques :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->treatment_recommendations ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET TRAITEMENTS ==================== --}}
    <div class="{{ $activeTab === 'treatments' ? '' : 'hidden' }}">
        <x-card title="Traitements quotidiens" icon="o-document-check" separator>
            <div class="flex justify-end mb-4">
                <x-button label="Ajouter un traitement" icon="o-plus" class="btn-sm btn-primary" wire:click="openCreateTreatmentModal" />
            </div>

            @if(count($treatments) > 0)
                <x-table :headers="[
                    ['key' => 'treatment_date', 'label' => 'Date'],
                    ['key' => 'treatment_time', 'label' => 'Heure'],
                    ['key' => 'care_provided', 'label' => 'Soins'],
                    ['key' => 'patient_condition', 'label' => 'État'],
                    ['key' => 'doctor.name', 'label' => 'Médecin'],
                    ['key' => 'cost', 'label' => 'Coût'],
                    ['key' => 'actions', 'label' => 'Actions'],
                ]" :rows="$treatments" striped>

                    @scope('cell_treatment_date', $treatment)
                        {{ \Carbon\Carbon::parse($treatment['treatment_date'])->format('d/m/Y') }}
                    @endscope
                    @scope('cell_treatment_time', $treatment)
                        {{ $treatment['treatment_time'] ? \Carbon\Carbon::parse($treatment['treatment_time'])->format('H:i') : '-' }}
                    @endscope
                    @scope('cell_patient_condition', $treatment)
                        @if($treatment['patient_condition'] === 'stable')
                            <x-badge value="Stable" class="badge-info badge-soft" />
                        @elseif($treatment['patient_condition'] === 'amélioré')
                            <x-badge value="Amélioré" class="badge-success badge-soft" />
                        @elseif($treatment['patient_condition'] === 'dégradé')
                            <x-badge value="Dégradé" class="badge-error badge-soft" />
                        @else
                            <x-badge value="-" class="badge-ghost" />
                        @endif
                    @endscope
                    @scope('actions', $treatment)
                        <div class="flex gap-1">
                            <x-button icon="o-eye" class="btn-circle btn-ghost btn-sm" wire:click="viewTreatment({{ $treatment['id'] }})" />
                            <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm" wire:click="openEditTreatmentModal({{ $treatment['id'] }})" />
                            <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm" wire:click="confirmDeleteTreatment({{ $treatment['id'] }})" />
                        </div>
                    @endscope
                </x-table>
            @else
                <div class="text-center py-8 text-base-content/60">
                    <x-icon name="o-document-check" class="h-12 w-12 mx-auto mb-2 opacity-30" />
                    <p>Aucun traitement enregistré pour cette consultation</p>
                    <x-button label="Ajouter un traitement" icon="o-plus" class="btn-sm btn-outline mt-3" wire:click="openCreateTreatmentModal" />
                </div>
            @endif
        </x-card>
    </div>

    {{-- ==================== ONGLET DOCUMENTS ==================== --}}
    <div class="{{ $activeTab === 'documents' ? '' : 'hidden' }}">
        <x-card title="Documents joints" icon="o-paper-clip" separator>
            @if($consultation->documents && count($consultation->documents) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($consultation->documents as $index => $doc)
                        <div class="flex justify-between items-center p-3 bg-base-200 rounded">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-document" class="h-5 w-5" />
                                <div>
                                    <div class="font-medium text-sm">{{ $doc['original_name'] }}</div>
                                    <div class="text-xs text-base-content/60">{{ number_format($doc['size'] / 1024, 1) }} KB</div>
                                </div>
                            </div>
                            <x-button icon="o-arrow-down-tray" class="btn-xs btn-ghost" wire:click="downloadDocument({{ $index }})" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-base-content/60">
                    <x-icon name="o-paper-clip" class="h-12 w-12 mx-auto mb-2 opacity-30" />
                    <p>Aucun document joint à cette consultation</p>
                </div>
            @endif
        </x-card>
    </div>

    {{-- ==================== MODAL TRAITEMENT ==================== --}}
    <x-modal wire:model="treatmentModal" title="{{ $modalTitle }}" size="3xl" separator>
        <x-form wire:submit="saveTreatment">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div><label class="label font-medium">Date *</label><input type="date" wire:model="treatment_date" class="input input-bordered w-full" required /></div>
                <div><label class="label font-medium">Heure</label><input type="time" wire:model="treatment_time" class="input input-bordered w-full" /></div>
            </div>
            <div class="tabs tabs-boxed mb-4">
                <a class="tab {{ $activeFormTab === 'care' ? 'tab-active' : '' }}" wire:click="$set('activeFormTab', 'care')">Soins</a>
                <a class="tab {{ $activeFormTab === 'evaluation' ? 'tab-active' : '' }}" wire:click="$set('activeFormTab', 'evaluation')">Évaluation</a>
                <a class="tab {{ $activeFormTab === 'financial' ? 'tab-active' : '' }}" wire:click="$set('activeFormTab', 'financial')">Finances</a>
            </div>
            <div class="{{ $activeFormTab === 'care' ? '' : 'hidden' }} space-y-4">
                <x-textarea label="Observations" wire:model="observations" rows="3" />
                <x-textarea label="Soins prodigués" wire:model="care_provided" rows="3" />
                <x-textarea label="Médicaments" wire:model="medications_given" rows="2" />
            </div>
            <div class="{{ $activeFormTab === 'evaluation' ? '' : 'hidden' }} space-y-4">
                <x-select label="État du patient" wire:model="patient_condition" :options="[['id' => 'stable', 'name' => 'Stable'], ['id' => 'amélioré', 'name' => 'Amélioré'], ['id' => 'dégradé', 'name' => 'Dégradé']]" option-value="id" option-label="name" placeholder="Sélectionner" />
                <x-textarea label="Notes du médecin" wire:model="doctor_notes" rows="3" />
                <x-textarea label="Instructions" wire:model="next_instructions" rows="2" />
            </div>
            <div class="{{ $activeFormTab === 'financial' ? '' : 'hidden' }}"><input type="number" wire:model="cost" class="input input-bordered w-full" step="100" placeholder="Coût (FCFA)" /></div>
            <x-slot:actions>
                <x-button label="Annuler" wire:click="$set('treatmentModal', false)" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="saveTreatment" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- ==================== MODAL VISUALISATION TRAITEMENT ==================== --}}
    <x-modal wire:model="viewTreatmentModal" title="Détail du traitement" size="3xl" separator>
        @if($viewingTreatment)
            <div class="grid grid-cols-2 gap-4 pb-3 border-b">
                <div><span class="font-bold">Date :</span> {{ \Carbon\Carbon::parse($viewingTreatment['treatment_date'])->format('d/m/Y') }}</div>
                <div><span class="font-bold">Heure :</span> {{ $viewingTreatment['treatment_time'] ? \Carbon\Carbon::parse($viewingTreatment['treatment_time'])->format('H:i') : '-' }}</div>
                <div><span class="font-bold">État :</span> {{ $viewingTreatment['patient_condition'] ?? '-' }}</div>
                <div><span class="font-bold">Coût :</span> {{ $viewingTreatment['cost'] ? number_format($viewingTreatment['cost'], 0, ',', ' ') . ' FCFA' : '-' }}</div>
            </div>
            @if($viewingTreatment['observations'])<div><span class="font-bold">Observations :</span><br>{{ $viewingTreatment['observations'] }}</div>@endif
            @if($viewingTreatment['care_provided'])<div class="mt-2"><span class="font-bold">Soins :</span><br>{{ $viewingTreatment['care_provided'] }}</div>@endif
            <x-slot:actions>
                <x-button label="Modifier" class="btn-primary" wire:click="openEditTreatmentModal({{ $viewingTreatment['id'] }})" />
                <x-button label="Fermer" wire:click="$set('viewTreatmentModal', false)" />
            </x-slot:actions>
        @endif
    </x-modal>

    {{-- ==================== MODAL SUPPRESSION ==================== --}}
    <x-modal wire:model="deleteModal" title="Confirmation" separator>
        <p>Supprimer ce traitement ?</p>
        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('deleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="deleteTreatment" spinner />
        </x-slot:actions>
    </x-modal>
</div>
