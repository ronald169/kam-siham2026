<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Medecine;
use App\Models\Treatment;
use Barryvdh\DomPDF\Facade\Pdf;

new
#[Title('Fiche consultation - Médecine Générale')]
class extends Component {
    use Toast;

    public Medecine $consultation;
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
        $this->consultation = Medecine::with(['patient', 'doctor'])->findOrFail($id);
        $this->loadTreatments();
    }

    public function loadTreatments()
    {
        $this->treatments = Treatment::where('treatable_type', 'App\\Models\\Medecine')
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
            'treatable_type' => 'App\\Models\\Medecine',
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
        $pdf = Pdf::loadView('pdf.medecine', ['consultation' => $this->consultation]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'medecine_' . $this->consultation->patient->medical_record_number . '_' . $this->consultation->consultation_date . '.pdf'
        );
    }

    public function render()
    {
        return $this->view();
    }
};

?>

<div>
    {{-- En-tête --}}
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="avatar placeholder">
                <div class="bg-primary text-primary-content rounded-full w-14">
                    <span class="text-xl font-bold">{{ substr($consultation->patient->name, 0, 1) }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Consultation Médecine Générale</h1>
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
            <x-button label="Modifier" icon="o-pencil" class="btn-outline" link="{{ route('consultations.medecine.edit', $consultation->id) }}" />
            <x-button label="Retour" icon="o-arrow-left" link="{{ route('consultations.medecine.index') }}" />
        </div>
    </div>

    {{-- Onglets --}}
    <div class="tabs tabs-boxed mb-6 overflow-x-auto flex-nowrap">
        <a class="tab {{ $activeTab === 'info' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'info')">Informations</a>
        <a class="tab {{ $activeTab === 'consultation' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consultation')">Consultation</a>
        <a class="tab {{ $activeTab === 'exams' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'exams')">Examens</a>
        <a class="tab {{ $activeTab === 'treatment' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatment')">Traitement</a>
        <a class="tab {{ $activeTab === 'followup' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'followup')">Suivi</a>
        <a class="tab {{ $activeTab === 'treatments' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatments')">Traitements ({{ count($treatments) }})</a>
        <a class="tab {{ $activeTab === 'documents' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'documents')">Documents</a>
    </div>

    {{-- Onglet Informations --}}
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

    {{-- Onglet Consultation --}}
    <div class="{{ $activeTab === 'consultation' ? '' : 'hidden' }}">
        <x-card title="Consultation" icon="o-clipboard-document-list" separator>
            <div><span class="font-medium">Motif de consultation :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->consultation_reason ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Histoire de la maladie :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->illness_history ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Antécédents :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->medical_history ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Examen physique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->physical_examination ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Hypothèse diagnostique :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->diagnostic_hypothesis ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- Onglet Examens --}}
    <div class="{{ $activeTab === 'exams' ? '' : 'hidden' }}">
        <x-card title="Examens demandés" icon="o-document-magnifying-glass" separator>
            <p class="bg-base-200 p-3 rounded">{{ $consultation->requested_exams ?? '-' }}</p>
        </x-card>
    </div>

    {{-- Onglet Traitement --}}
    <div class="{{ $activeTab === 'treatment' ? '' : 'hidden' }}">
        <x-card title="Traitements" icon="o-beaker" separator>
            <div><span class="font-medium">Traitements prescrits :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->prescribed_treatments ?? '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Ordonnance :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->medical_prescriptions ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- Onglet Suivi --}}
    <div class="{{ $activeTab === 'followup' ? '' : 'hidden' }}">
        <x-card title="Suivi" icon="o-calendar" separator>
            <div><span class="font-medium">Prochain rendez-vous :</span><p>{{ $consultation->next_appointment ? \Carbon\Carbon::parse($consultation->next_appointment)->format('d/m/Y') : '-' }}</p></div>
            <div class="mt-4"><span class="font-medium">Instructions de suivi :</span><p class="mt-2 bg-base-200 p-3 rounded">{{ $consultation->follow_up_instructions ?? '-' }}</p></div>
        </x-card>
    </div>

    {{-- Onglet Traitements quotidiens --}}
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

    {{-- Onglet Documents --}}
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

    {{-- Modals (Traitement) --}}
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

    <x-modal wire:model="deleteModal" title="Confirmation" separator>
        <p>Supprimer ce traitement ?</p>
        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('deleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="deleteTreatment" spinner />
        </x-slot:actions>
    </x-modal>
</div>
