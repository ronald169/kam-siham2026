<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Toxicology;
use App\Models\Treatment;
use Barryvdh\DomPDF\Facade\Pdf;

new
#[Title('Fiche consultation - Toxicologie')]
class extends Component {
    use Toast;

    public Toxicology $consultation;
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
        $this->consultation = Toxicology::with(['patient', 'doctor'])->findOrFail($id);
        $this->loadTreatments();
    }

    public function loadTreatments()
    {
        $this->treatments = Treatment::where('treatable_type', 'App\\Models\\Toxicology')
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

    public function viewTreatment($id)
    {
        $treatment = Treatment::with(['patient', 'doctor'])->findOrFail($id);
        $this->viewingTreatment = $treatment->toArray();
        $this->viewTreatmentModal = true;
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

    public function saveTreatment()
    {
        $this->validate([
            'treatment_date' => 'required|date',
            'observations' => 'nullable|string',
            'patient_condition' => 'nullable|string|in:stable,amélioré,dégradé',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'treatable_type' => 'App\\Models\\Toxicology',
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
        $pdf = Pdf::loadView('pdf.toxicologie', ['consultation' => $this->consultation]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'toxicologie_' . $this->consultation->patient->medical_record_number . '_' . $this->consultation->consultation_date . '.pdf'
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
                <h1 class="text-2xl font-bold">Consultation Toxicologie</h1>
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
            <x-button label="Modifier" icon="o-pencil" class="btn-outline" link="{{ route('consultations.toxicologie.edit', $consultation->id) }}" />
            <x-button label="Retour" icon="o-arrow-left" link="{{ route('consultations.toxicologie.index') }}" />
        </div>
    </div>

    {{-- ==================== ONGLETS ==================== --}}
    <div class="tabs tabs-boxed mb-6 overflow-x-auto flex-nowrap">
        <a class="tab {{ $activeTab === 'info' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'info')">
            <x-icon name="o-information-circle" class="h-5 w-5 mr-2" /> Informations
        </a>
        <a class="tab {{ $activeTab === 'history' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'history')">
            <x-icon name="o-document-text" class="h-5 w-5 mr-2" /> Histoire
        </a>
        <a class="tab {{ $activeTab === 'consequences' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consequences')">
            <x-icon name="o-exclamation-triangle" class="h-5 w-5 mr-2" /> Conséquences
        </a>
        <a class="tab {{ $activeTab === 'evaluation' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'evaluation')">
            <x-icon name="o-chart-bar" class="h-5 w-5 mr-2" /> Évaluation
        </a>
        <a class="tab {{ $activeTab === 'clinical' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'clinical')">
            <x-icon name="o-heart" class="h-5 w-5 mr-2" /> Clinique
        </a>
        <a class="tab {{ $activeTab === 'antecedents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'antecedents')">
            <x-icon name="o-folder" class="h-5 w-5 mr-2" /> Antécédents
        </a>
        <a class="tab {{ $activeTab === 'exams' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'exams')">
            <x-icon name="o-document-magnifying-glass" class="h-5 w-5 mr-2" /> Examens
        </a>
        <a class="tab {{ $activeTab === 'conclusion' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'conclusion')">
            <x-icon name="o-document-check" class="h-5 w-5 mr-2" /> Conclusion
        </a>
        <a class="tab {{ $activeTab === 'treatments' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatments')">
            <x-icon name="o-beaker" class="h-5 w-5 mr-2" /> Traitements ({{ count($treatments) }})
        </a>
        <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">
            <x-icon name="o-paper-clip" class="h-5 w-5 mr-2" /> Documents
        </a>
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
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Dernière modification :</span><span>{{ $consultation->updated_at ? $consultation->updated_at->format('d/m/Y H:i') : '-' }}</span></div>
                </div>
            </x-card>

            <x-card title="Patient" icon="o-user" separator>
                <div class="space-y-2">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nom complet :</span><span>{{ $consultation->patient->name }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">N° Dossier :</span><span>{{ $consultation->patient->medical_record_number }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Âge :</span><span>{{ $consultation->patient->age ?? '-' }} ans</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Sexe :</span><span>{{ $consultation->patient->sex ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Téléphone :</span><span>{{ $consultation->patient->patient_phone ?? '-' }}</span></div>
                </div>
                <x-slot:actions>
                    <x-button label="Voir dossier patient" icon="o-eye" class="btn-ghost btn-sm" link="{{ route('patients.show', $consultation->patient->id) }}" />
                </x-slot:actions>
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET HISTOIRE ADDICTIVE ==================== --}}
    <div class="{{ $activeTab === 'history' ? '' : 'hidden' }}">
        <x-card title="Histoire des conduites addictives" icon="o-beaker" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Substances consommées :</span><span>{{ $consultation->substances_used ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Âge de début :</span><span>{{ $consultation->substances_start_age ?? '-' }} ans</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Raison du début :</span><span>{{ $consultation->substances_start_reason ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Motivation actuelle :</span><span>{{ $consultation->current_consumption_motivation ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Tolérance :</span><span>{{ $consultation->tolerance_description ?? '-' }}</span></div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Tentatives d'arrêt :</span><span>{{ $consultation->withdrawal_attempts ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Motivation à arrêter :</span><span>{{ $consultation->stop_motivation ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Types de substances :</span><span>{{ $consultation->substances_types ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Durée max abstinence :</span><span>{{ $consultation->max_abstinence_duration ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Relation substance :</span><span>{{ $consultation->substance_relation ?? '-' }}</span></div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET CONSÉQUENCES ==================== --}}
    <div class="{{ $activeTab === 'consequences' ? '' : 'hidden' }}">
        <x-card title="Conséquences de la consommation" icon="o-exclamation-triangle" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex justify-between"><span class="font-medium">Amaigrissement :</span><x-badge :value="$consultation->weight_loss ? 'Oui' : 'Non'" :class="$consultation->weight_loss ? 'badge-error' : 'badge-success'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Teint sombre :</span><x-badge :value="$consultation->pale_complexion ? 'Oui' : 'Non'" :class="$consultation->pale_complexion ? 'badge-error' : 'badge-success'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Insomnie de manque :</span><x-badge :value="$consultation->withdrawal_insomnia ? 'Oui' : 'Non'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Cauchemars :</span><x-badge :value="$consultation->nightmares ? 'Oui' : 'Non'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Hallucinations :</span><x-badge :value="$consultation->hallucinations ? 'Oui' : 'Non'" /></div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between"><span class="font-medium">Troubles somatiques :</span><x-badge :value="$consultation->somatic_disorders ? 'Oui' : 'Non'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Délire comportement :</span><x-badge :value="$consultation->behavioral_delirium ? 'Oui' : 'Non'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Problèmes judiciaires :</span><x-badge :value="$consultation->legal_issues ? 'Oui' : 'Non'" /></div>
                    <div class="flex justify-between"><span class="font-medium">Épanouissement affectif :</span><span>{{ $consultation->affective_fulfillment ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="font-medium">Épanouissement sexuel :</span><span>{{ $consultation->sexual_fulfillment ?? '-' }}</span></div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET ÉVALUATION ==================== --}}
    <div class="{{ $activeTab === 'evaluation' ? '' : 'hidden' }}">
        <x-card title="Évaluation de la dépendance" icon="o-chart-bar" separator>
            <div><span class="font-medium">Pattern de consommation :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->consumption_pattern ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Investissement dans la dépendance :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->dependency_investment ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET TABLEAU CLINIQUE ==================== --}}
    <div class="{{ $activeTab === 'clinical' ? '' : 'hidden' }}">
        <x-card title="Tableau clinique" icon="o-heart" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">État général :</span><p class="mt-1">{{ $consultation->general_condition ?? '-' }}</p></div>
                <div><span class="font-medium">Signes respiratoires :</span><p class="mt-1">{{ $consultation->respiratory_signs ?? '-' }}</p></div>
                <div><span class="font-medium">Signes neurologiques :</span><p class="mt-1">{{ $consultation->neurological_signs ?? '-' }}</p></div>
                <div><span class="font-medium">Troubles psychiatriques :</span><p class="mt-1">{{ $consultation->psychiatric_disorders ?? '-' }}</p></div>
                <div class="md:col-span-2"><span class="font-medium">Autres symptômes :</span><p class="mt-1">{{ $consultation->other_symptoms ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET ANTÉCÉDENTS ==================== --}}
    <div class="{{ $activeTab === 'antecedents' ? '' : 'hidden' }}">
        <x-card title="Antécédents" icon="o-folder" separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">Médico-chirurgicaux :</span><p class="mt-1">{{ $consultation->medical_surgical_history ?? '-' }}</p></div>
                <div><span class="font-medium">Allergiques :</span><p class="mt-1">{{ $consultation->allergy_history ?? '-' }}</p></div>
                <div><span class="font-medium">Psychiatriques :</span><p class="mt-1">{{ $consultation->psychiatric_history ?? '-' }}</p></div>
                <div><span class="font-medium">Traumatiques :</span><p class="mt-1">{{ $consultation->trauma_history ?? '-' }}</p></div>
            </div>
        </x-card>
    </div>

    {{-- ==================== ONGLET EXAMENS ==================== --}}
    <div class="{{ $activeTab === 'exams' ? '' : 'hidden' }}">
        <x-card title="Examens complémentaires" icon="o-document-magnifying-glass" separator>
            <div><span class="font-medium">Bilan psychologique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->psychological_assessment ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Bilan biologique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->biological_assessment ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET CONCLUSION ET CAT ==================== --}}
    <div class="{{ $activeTab === 'conclusion' ? '' : 'hidden' }}">
        <x-card title="Conclusion et Conduite à tenir" icon="o-document-check" separator>
            <div><span class="font-medium">Conclusion diagnostique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->diagnostic_conclusion ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Plan de traitement :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->treatment_plan ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Recommandations :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->recommendations ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- ==================== ONGLET TRAITEMENTS ==================== --}}
    <div class="{{ $activeTab === 'treatments' ? '' : 'hidden' }}">
        <x-card title="Traitements quotidiens" icon="o-beaker" separator>
            <div class="flex justify-end mb-4">
                <x-button label="Ajouter un traitement" icon="o-plus" class="btn-sm btn-primary" wire:click="openCreateTreatmentModal" />
            </div>

            @if(count($treatments) > 0)

            <x-table
                :headers="[
                    ['key' => 'treatment_date', 'label' => 'Date'],
                    ['key' => 'treatment_time', 'label' => 'Heure'],
                    ['key' => 'care_provided', 'label' => 'Soins'],
                    ['key' => 'patient_condition', 'label' => 'État'],
                    ['key' => 'doctor.name', 'label' => 'Médecin'],
                    {{-- ['key' => 'cost', 'label' => 'Coût'], --}}
                    ['key' => 'actions', 'label' => 'Actions'],
                ]"
                :rows="$treatments"
                striped>

                @scope('cell_treatment_date', $treatment)
                    {{ \Carbon\Carbon::parse($treatment['treatment_date'])->format('d/m/Y') }}
                @endscope

                @scope('cell_treatment_time', $treatment)
                    {{ $treatment['treatment_time'] ? \Carbon\Carbon::parse($treatment['treatment_time'])->format('H:i') : '-' }}
                @endscope

                @scope('cell_care_provided', $treatment)
                    {{ \Illuminate\Support\Str::limit($treatment['care_provided'] ?? $treatment['observations'], 50) }}
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

                {{-- @scope('cell_cost', $treatment)
                    {{ $treatment['cost'] ? number_format($treatment['cost'], 0, ',', ' ') . ' FCFA' : '-' }}
                @endscope --}}

                {{-- Actions avec le bouton Voir --}}
                @scope('actions', $treatment)
                    <div class="flex gap-1">
                        <x-button icon="o-eye" class="btn-circle btn-ghost btn-sm"
                            tooltip-left="Voir les détails"
                            wire:click="viewTreatment({{ $treatment['id'] }})" />

                        <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm"
                            tooltip-left="Modifier"
                            wire:click="openEditTreatmentModal({{ $treatment['id'] }})" />

                        <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm"
                            tooltip-left="Supprimer"
                            wire:click="confirmDeleteTreatment({{ $treatment['id'] }})" />
                    </div>
                @endscope
            </x-table>

            @else
                <div class="text-center py-8 text-base-content/60">
                    <x-icon name="o-beaker" class="h-12 w-12 mx-auto mb-2 opacity-30" />
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

    {{-- ==================== MODAL VISUALISATION TRAITEMENT ==================== --}}
    <x-modal wire:model="viewTreatmentModal" title="Détail du traitement" size="3xl" separator>
        @if($viewingTreatment)
            <div class="space-y-4">
                {{-- Contenu du modal --}}
                <div class="grid grid-cols-2 gap-4 pb-3 border-b">
                    <div><span class="font-bold">Date :</span> {{ \Carbon\Carbon::parse($viewingTreatment['treatment_date'])->format('d/m/Y') }}</div>
                    <div><span class="font-bold">Heure :</span> {{ $viewingTreatment['treatment_time'] ? \Carbon\Carbon::parse($viewingTreatment['treatment_time'])->format('H:i') : '-' }}</div>
                    <div><span class="font-bold">Patient :</span> {{ $viewingTreatment['patient']['name'] ?? '-' }}</div>
                    <div><span class="font-bold">Médecin :</span> {{ $viewingTreatment['doctor']['name'] ?? '-' }}</div>
                    <div><span class="font-bold">État :</span>
                        @if($viewingTreatment['patient_condition'] === 'stable')
                            <x-badge value="Stable" class="badge-info badge-soft" />
                        @elseif($viewingTreatment['patient_condition'] === 'amélioré')
                            <x-badge value="Amélioré" class="badge-success badge-soft" />
                        @elseif($viewingTreatment['patient_condition'] === 'dégradé')
                            <x-badge value="Dégradé" class="badge-error badge-soft" />
                        @else
                            <x-badge value="-" class="badge-ghost" />
                        @endif
                    </div>
                    <div><span class="font-bold">Coût :</span> {{ $viewingTreatment['cost'] ? number_format($viewingTreatment['cost'], 0, ',', ' ') . ' FCFA' : '-' }}</div>
                </div>

                @if($viewingTreatment['observations'])
                <div>
                    <span class="font-bold block mb-1">📋 Observations :</span>
                    <div class="bg-base-200 p-3 rounded">{{ $viewingTreatment['observations'] }}</div>
                </div>
                @endif

                @if($viewingTreatment['care_provided'])
                <div>
                    <span class="font-bold block mb-1">🩺 Soins prodigués :</span>
                    <div class="bg-base-200 p-3 rounded">{{ $viewingTreatment['care_provided'] }}</div>
                </div>
                @endif

                @if($viewingTreatment['medications_given'])
                <div>
                    <span class="font-bold block mb-1">💊 Médicaments administrés :</span>
                    <div class="bg-base-200 p-3 rounded">{{ $viewingTreatment['medications_given'] }}</div>
                </div>
                @endif

                @if($viewingTreatment['doctor_notes'])
                <div>
                    <span class="font-bold block mb-1">📝 Notes du médecin :</span>
                    <div class="bg-base-200 p-3 rounded">{{ $viewingTreatment['doctor_notes'] }}</div>
                </div>
                @endif

                @if($viewingTreatment['next_instructions'])
                <div>
                    <span class="font-bold block mb-1">📌 Instructions :</span>
                    <div class="bg-base-200 p-3 rounded">{{ $viewingTreatment['next_instructions'] }}</div>
                </div>
                @endif
            </div>

            <x-slot:actions>
                <x-button label="Modifier" icon="o-pencil" class="btn-primary"
                    wire:click="openEditTreatmentModal({{ $viewingTreatment['id'] }})" />
                <x-button label="Fermer" wire:click="$set('viewTreatmentModal', false)" />
            </x-slot:actions>
        @endif
    </x-modal>

    {{-- ==================== MODAL TRAITEMENT (AJOUT/MODIFICATION) ==================== --}}
    <x-modal wire:model="treatmentModal" title="{{ $modalTitle }}" size="3xl" separator>
        <x-form wire:submit="saveTreatment">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Date *</span></label>
                    <input type="date" wire:model="treatment_date" class="input input-bordered" required />
                    @error('treatment_date') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Heure</span></label>
                    <input type="time" wire:model="treatment_time" class="input input-bordered" />
                </div>
            </div>

            <div class="tabs tabs-boxed mb-4">
                <a class="tab {{ $activeFormTab === 'care' ? 'tab-active' : '' }}" wire:click="$set('activeFormTab', 'care')">
                    <x-icon name="o-heart" class="h-5 w-5 mr-2" /> Soins
                </a>
                <a class="tab {{ $activeFormTab === 'evaluation' ? 'tab-active' : '' }}" wire:click="$set('activeFormTab', 'evaluation')">
                    <x-icon name="o-clipboard-document-list" class="h-5 w-5 mr-2" /> Évaluation
                </a>
                <a class="tab {{ $activeFormTab === 'financial' ? 'tab-active' : '' }}" wire:click="$set('activeFormTab', 'financial')">
                    <x-icon name="o-currency-dollar" class="h-5 w-5 mr-2" /> Finances
                </a>
            </div>

            {{-- Onglet Soins --}}
            <div class="{{ $activeFormTab === 'care' ? '' : 'hidden' }} space-y-4">
                <x-textarea
                    label="Observations"
                    wire:model="observations"
                    rows="3"
                    placeholder="Observations du jour, état général..." />

                <x-textarea
                    label="Soins prodigués"
                    wire:model="care_provided"
                    rows="3"
                    placeholder="Soins administrés, pansements, nursing..." />

                <x-textarea
                    label="Médicaments administrés"
                    wire:model="medications_given"
                    rows="2"
                    placeholder="Médicaments donnés, posologie..." />
            </div>

            {{-- Onglet Évaluation --}}
            <div class="{{ $activeFormTab === 'evaluation' ? '' : 'hidden' }} space-y-4">
                <x-select
                    label="État du patient"
                    wire:model="patient_condition"
                    :options="[
                        ['id' => 'stable', 'name' => 'Stable'],
                        ['id' => 'amélioré', 'name' => 'Amélioré'],
                        ['id' => 'dégradé', 'name' => 'Dégradé'],
                    ]"
                    option-value="id"
                    option-label="name"
                    placeholder="Sélectionner l'état"
                    id="patient_condition"
                    name="patient_condition" />

                <x-textarea
                    label="Notes du médecin"
                    wire:model="doctor_notes"
                    rows="3"
                    placeholder="Notes médicales, évolution..." />

                <x-textarea
                    label="Instructions prochain traitement"
                    wire:model="next_instructions"
                    rows="2"
                    placeholder="À faire pour le prochain passage..." />
            </div>

            {{-- Onglet Finances --}}
            <div class="{{ $activeFormTab === 'financial' ? '' : 'hidden' }} space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Coût (FCFA)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60">FCFA</span>
                        <input type="number" wire:model="cost" class="input input-bordered w-full pl-20" step="100" min="0" placeholder="0" />
                        @error('cost') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                    <span class="label-text-alt text-base-content/50 mt-1">Montant facturé pour ce traitement</span>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Annuler" wire:click="$set('treatmentModal', false)" />
                <x-button label="{{ $editingTreatmentId ? 'Modifier' : 'Enregistrer' }}" class="btn-primary" type="submit" spinner="saveTreatment" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- ==================== MODAL CONFIRMATION SUPPRESSION ==================== --}}
    <x-modal wire:model="deleteModal" title="Confirmation" separator>
        <p>Êtes-vous sûr de vouloir supprimer ce traitement ? Cette action est irréversible.</p>
        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('deleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="deleteTreatment" spinner="deleteTreatment" />
        </x-slot:actions>
    </x-modal>
</div>
