<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Toxicology;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;

new
#[Title('Consultations - Toxicologie')]
class extends Component {
    use WithPagination, WithFileUploads, Toast;

    // Filtres
    public string $search = '';
    public ?int $patient_id = null;
    public array $sortBy = ['column' => 'consultation_date', 'direction' => 'desc'];

    // Formulaire
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $patientId = null;
    public string $consultation_date = '';

    // Histoire des conduites addictives
    public string $substances_used = '';
    public ?int $substances_start_age = null;
    public string $substances_start_reason = '';
    public string $current_consumption_motivation = '';
    public string $tolerance_description = '';
    public string $withdrawal_attempts = '';
    public string $stop_motivation = '';
    public string $max_abstinence_duration = '';

    // Conséquences
    public bool $weight_loss = false;
    public bool $pale_complexion = false;
    public bool $withdrawal_insomnia = false;
    public bool $nightmares = false;
    public bool $hallucinations = false;
    public bool $somatic_disorders = false;
    public bool $behavioral_delirium = false;
    public bool $legal_issues = false;

    // Évaluation et conclusion
    public string $diagnostic_conclusion = '';
    public string $treatment_plan = '';
    public string $recommendations = '';

    // Documents
    public $documents = [];
    public array $existingDocuments = [];

    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get(['id', 'name', 'medical_record_number']);
    }

    public function getConsultationsProperty()
    {
        return Toxicology::query()
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
        $consultation = Toxicology::findOrFail($id);
        $this->editingId = $id;
        $this->patientId = $consultation->patient_id;
        $this->consultation_date = $consultation->consultation_date;
        $this->substances_used = $consultation->substances_used;
        $this->substances_start_age = $consultation->substances_start_age;
        $this->substances_start_reason = $consultation->substances_start_reason;
        $this->current_consumption_motivation = $consultation->current_consumption_motivation;
        $this->tolerance_description = $consultation->tolerance_description;
        $this->withdrawal_attempts = $consultation->withdrawal_attempts;
        $this->stop_motivation = $consultation->stop_motivation;
        $this->max_abstinence_duration = $consultation->max_abstinence_duration;
        $this->weight_loss = $consultation->weight_loss ?? false;
        $this->pale_complexion = $consultation->pale_complexion ?? false;
        $this->withdrawal_insomnia = $consultation->withdrawal_insomnia ?? false;
        $this->nightmares = $consultation->nightmares ?? false;
        $this->hallucinations = $consultation->hallucinations ?? false;
        $this->somatic_disorders = $consultation->somatic_disorders ?? false;
        $this->behavioral_delirium = $consultation->behavioral_delirium ?? false;
        $this->legal_issues = $consultation->legal_issues ?? false;
        $this->diagnostic_conclusion = $consultation->diagnostic_conclusion;
        $this->treatment_plan = $consultation->treatment_plan;
        $this->recommendations = $consultation->recommendations;
        $this->existingDocuments = $consultation->documents ?? [];
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'substances_used' => 'nullable|string',
            'diagnostic_conclusion' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'substances_used' => $this->substances_used,
            'substances_start_age' => $this->substances_start_age,
            'substances_start_reason' => $this->substances_start_reason,
            'current_consumption_motivation' => $this->current_consumption_motivation,
            'tolerance_description' => $this->tolerance_description,
            'withdrawal_attempts' => $this->withdrawal_attempts,
            'stop_motivation' => $this->stop_motivation,
            'max_abstinence_duration' => $this->max_abstinence_duration,
            'weight_loss' => $this->weight_loss,
            'pale_complexion' => $this->pale_complexion,
            'withdrawal_insomnia' => $this->withdrawal_insomnia,
            'nightmares' => $this->nightmares,
            'hallucinations' => $this->hallucinations,
            'somatic_disorders' => $this->somatic_disorders,
            'behavioral_delirium' => $this->behavioral_delirium,
            'legal_issues' => $this->legal_issues,
            'diagnostic_conclusion' => $this->diagnostic_conclusion,
            'treatment_plan' => $this->treatment_plan,
            'recommendations' => $this->recommendations,
            'status' => 'completed',
        ];

        if ($this->editingId) {
            $consultation = Toxicology::findOrFail($this->editingId);
            $consultation->update($data);
            $this->success('Consultation modifiée avec succès.');
        } else {
            $consultation = Toxicology::create($data);
            $this->success('Consultation créée avec succès.');
        }

        // Gestion des nouveaux fichiers
        if (!empty($this->documents)) {
            $documents = $consultation->documents ?? [];
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$this->patientId}/toxicologie", 'public');
                $documents[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString()
                ];
            }
            $consultation->documents = $documents;
            $consultation->save();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deleteDocument($index)
    {
        $consultation = Toxicology::findOrFail($this->editingId);
        $documents = $consultation->documents ?? [];
        if (isset($documents[$index])) {
            \Storage::disk('public')->delete($documents[$index]['path']);
            array_splice($documents, $index, 1);
            $consultation->documents = $documents;
            $consultation->save();
            $this->existingDocuments = $documents;
            $this->success('Document supprimé avec succès.');
        }
    }

    public function downloadDocument($index)
    {
        $consultation = Toxicology::findOrFail($this->editingId);
        $documents = $consultation->documents ?? [];
        if (isset($documents[$index])) {
            return response()->download(storage_path('app/public/' . $documents[$index]['path']), $documents[$index]['original_name']);
        }
    }

    public function downloadPdf($id)
    {
        $consultation = Toxicology::with(['patient', 'doctor'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.toxicologie', ['consultation' => $consultation]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'toxicologie_' . $consultation->patient->medical_record_number . '_' . $consultation->consultation_date . '.pdf'
        );
    }

    public function delete($id)
    {
        $consultation = Toxicology::findOrFail($id);
        // Supprimer les fichiers associés
        if ($consultation->documents) {
            foreach ($consultation->documents as $doc) {
                \Storage::disk('public')->delete($doc['path']);
            }
        }
        $consultation->delete();
        $this->success('Consultation supprimée avec succès.');
    }

    public function resetForm()
    {
        $this->reset([
            'editingId', 'patientId', 'consultation_date',
            'substances_used', 'substances_start_age', 'substances_start_reason',
            'current_consumption_motivation', 'tolerance_description', 'withdrawal_attempts',
            'stop_motivation', 'max_abstinence_duration',
            'weight_loss', 'pale_complexion', 'withdrawal_insomnia', 'nightmares',
            'hallucinations', 'somatic_disorders', 'behavioral_delirium', 'legal_issues',
            'diagnostic_conclusion', 'treatment_plan', 'recommendations', 'documents', 'existingDocuments'
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
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Consultations Toxicologie</h1>
            <p class="text-base-content/70 mt-1">Gestion des évaluations en addictologie</p>
        </div>
        <x-button label="Nouvelle consultation" icon="o-plus" class="btn-primary" wire:click="create" />
    </div>

    {{-- Filtres --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-select
                label="Patient"
                wire:model.live="patient_id"
                :options="$patients"
                option-value="id"
                option-label="name"
                placeholder="Tous les patients"
                id="patient_id"
                name="patient_id"
                clearable />

            <x-input
                label="Recherche"
                icon="o-magnifying-glass"
                placeholder="Nom du patient..."
                wire:model.live.debounce.300ms="search"
                clearable />
        </div>
    </x-card>

    {{-- Tableau --}}
    <x-card>
        <x-table
            :headers="[
                ['key' => 'consultation_date', 'label' => 'Date'],
                ['key' => 'patient.name', 'label' => 'Patient'],
                ['key' => 'substances_used', 'label' => 'Substances'],
                ['key' => 'doctor.name', 'label' => 'Médecin'],
                ['key' => 'diagnostic_conclusion', 'label' => 'Conclusion'],
            ]"
            :rows="$consultations"
            :sort-by="$sortBy"
            with-pagination
            striped>

            @scope('cell_consultation_date', $consultation)
                {{ \Carbon\Carbon::parse($consultation['consultation_date'])->format('d/m/Y') }}
            @endscope

            @scope('cell_substances_used', $consultation)
                {{ Str::limit($consultation['substances_used'], 40) ?? '-' }}
            @endscope

            @scope('actions', $consultation)
                <div class="flex gap-1">
                    <x-button icon="o-arrow-down-tray" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Télécharger PDF" wire:click="downloadPdf({{ $consultation['id'] }})" spinner />
                    <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Modifier" wire:click="edit({{ $consultation['id'] }})" />
                    <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Supprimer" wire:click="delete({{ $consultation['id'] }})"
                        wire:confirm="Supprimer cette consultation ?" />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal Formulaire --}}
    <x-modal wire:model="showModal" title="{{ $editingId ? 'Modifier la consultation' : 'Nouvelle consultation' }}" size="4xl" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <x-select
                    label="Patient"
                    wire:model="patientId"
                    :options="$patients"
                    option-value="id"
                    option-label="name"
                    required
                    id="patientId"
                    name="patientId" />

                <x-datepicker
                    label="Date de consultation"
                    wire:model="consultation_date"
                    required
                    id="consultation_date"
                    name="consultation_date" />
            </div>

            {{-- Onglets --}}
            <div class="tabs tabs-boxed mb-4">
                <a class="tab tab-active" onclick="event.preventDefault(); showTab('tab-history')">Histoire addictive</a>
                <a class="tab" onclick="event.preventDefault(); showTab('tab-consequences')">Conséquences</a>
                <a class="tab" onclick="event.preventDefault(); showTab('tab-conclusion')">Conclusion & CAT</a>
                <a class="tab" onclick="event.preventDefault(); showTab('tab-documents')">Documents</a>
            </div>

            {{-- Onglet Histoire --}}
            <div id="tab-history" class="space-y-4">
                <x-textarea label="Substances consommées" wire:model="substances_used" rows="2" />
                <x-input label="Âge de début" wire:model="substances_start_age" type="number" />
                <x-textarea label="Raison du début" wire:model="substances_start_reason" rows="2" />
                <x-textarea label="Motivation actuelle" wire:model="current_consumption_motivation" rows="2" />
                <x-textarea label="Description de la tolérance" wire:model="tolerance_description" rows="2" />
                <x-textarea label="Tentatives d'arrêt" wire:model="withdrawal_attempts" rows="2" />
                <x-textarea label="Motivation à arrêter" wire:model="stop_motivation" rows="2" />
                <x-input label="Durée max d'abstinence" wire:model="max_abstinence_duration" />
            </div>

            {{-- Onglet Conséquences --}}
            <div id="tab-consequences" class="space-y-4 hidden">
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
            </div>

            {{-- Onglet Conclusion --}}
            <div id="tab-conclusion" class="space-y-4 hidden">
                <x-textarea label="Conclusion diagnostique" wire:model="diagnostic_conclusion" rows="3" />
                <x-textarea label="Plan de traitement" wire:model="treatment_plan" rows="3" />
                <x-textarea label="Recommandations" wire:model="recommendations" rows="2" />
            </div>

            {{-- Onglet Documents --}}
            <div id="tab-documents" class="space-y-4 hidden">
                {{-- Documents existants --}}
                @if(count($existingDocuments) > 0)
                    <div class="space-y-2">
                        <label class="label"><span class="label-text font-medium">Documents joints</span></label>
                        @foreach($existingDocuments as $index => $doc)
                            <div class="flex justify-between items-center p-2 bg-base-200 rounded">
                                <div class="flex items-center gap-2">
                                    <x-icon name="o-document" class="h-5 w-5" />
                                    <span>{{ $doc['original_name'] }}</span>
                                    <span class="text-xs text-base-content/60">{{ number_format($doc['size'] / 1024, 1) }} KB</span>
                                </div>
                                <div class="flex gap-1">
                                    <x-button icon="o-arrow-down-tray" class="btn-xs btn-ghost"
                                        wire:click="downloadDocument({{ $index }})" />
                                    <x-button icon="o-trash" class="btn-xs btn-ghost text-error"
                                        wire:click="deleteDocument({{ $index }})"
                                        wire:confirm="Supprimer ce document ?" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <x-file
                    label="Ajouter des documents"
                    wire:model="documents"
                    accept="pdf,jpg,jpeg,png,doc,docx"
                    multiple
                    hint="PDF, Images, Documents (Max 10MB)" />
            </div>

            <x-slot:actions>
                <x-button label="Annuler" wire:click="$set('showModal', false)" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('#tab-history, #tab-consequences, #tab-conclusion, #tab-documents').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.getElementById(tabId).classList.remove('hidden');
        document.querySelectorAll('.tabs a').forEach(t => t.classList.remove('tab-active'));
        event.target.classList.add('tab-active');
    }
</script>
