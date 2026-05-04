<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Treatment;
use App\Models\Patient;

new
#[Title('Traitements quotidiens')]
class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public ?int $patient_id = null;
    public string $service_type = '';
    public string $date_filter = '';
    public array $sortBy = ['column' => 'treatment_date', 'direction' => 'desc'];

    // Modals
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    public ?array $viewingTreatment = null;
    public ?int $treatmentToDelete = null;

    public function getPatientsProperty()
    {
        return Patient::orderBy('name')->get(['id', 'name', 'medical_record_number']);
    }

    public function getServiceTypesProperty()
    {
        return [
            ['id' => 'toxicologie', 'name' => 'Toxicologie'],
            ['id' => 'psychopathologie', 'name' => 'Psychopathologie'],
            ['id' => 'medecine', 'name' => 'Médecine Générale'],
        ];
    }

    public function getTreatmentsProperty()
    {
        $query = Treatment::query()
            ->with(['patient', 'doctor', 'treatable'])
            ->when($this->patient_id, fn($q) => $q->where('patient_id', $this->patient_id))
            ->when($this->service_type, function($q) {
                $class = match($this->service_type) {
                    'toxicologie' => 'App\\Models\\Toxicology',
                    'psychopathologie' => 'App\\Models\\Psychopathology',
                    'medecine' => 'App\\Models\\Medecine',
                    default => null
                };
                if ($class) $q->where('treatable_type', $class);
            })
            ->when($this->date_filter === 'today', fn($q) => $q->whereDate('treatment_date', today()))
            ->when($this->date_filter === 'week', fn($q) => $q->whereBetween('treatment_date', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->search, fn($q) => $q->whereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        if (auth()->user()->isMedecin()) {
            $query->where('doctor_id', auth()->id());
        }

        return $query->paginate(15);
    }

    public function viewTreatment($id)
    {
        $treatment = Treatment::with(['patient', 'doctor', 'treatable'])->findOrFail($id);
        $this->viewingTreatment = $treatment->toArray();
        $this->showViewModal = true;
    }

    public function editTreatment($id)
    {
        $treatment = Treatment::findOrFail($id);
        $typeMap = [
            'App\\Models\\Toxicology' => 'toxicologie',
            'App\\Models\\Psychopathology' => 'psychopathologie',
            'App\\Models\\Medecine' => 'medecine',
        ];
        $type = $typeMap[$treatment->treatable_type] ?? null;

        if ($type && $treatment->treatable_id) {
            return redirect()->route('consultations.' . $type . '.show', $treatment->treatable_id);
        } else {
            return redirect()->route('treatments.edit', $treatment->id);
        }
    }

    public function confirmDelete($id)
    {
        $this->treatmentToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteTreatment()
    {
        $treatment = Treatment::find($this->treatmentToDelete);
        if ($treatment) {
            $treatment->delete();
            $this->success('Traitement supprimé avec succès.');
        }
        $this->showDeleteModal = false;
        $this->treatmentToDelete = null;
    }

    public function render()
    {
        return $this->view([
            'patients' => $this->patients,
            'treatments' => $this->treatments,
            'serviceTypes' => $this->serviceTypes,
        ]);
    }
};

?>

<div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Traitements quotidiens</h1>
            <p class="text-base-content/70 mt-1">Historique des soins et traitements</p>
        </div>
    </div>

    {{-- Filtres --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-choices-offline
                label="Patient"
                wire:model.live="patient_id"
                :options="$patients"
                option-value="id"
                option-label="name"
                placeholder="Tous les patients"
                id="patient_id"
                name="patient_id"
                single
                clearable
                searchable />

            <x-select
                label="Service"
                wire:model.live="service_type"
                :options="$serviceTypes"
                option-value="id"
                option-label="name"
                placeholder="Tous les services"
                id="service_type"
                name="service_type"
                clearable />

            <x-select
                label="Période"
                wire:model.live="date_filter"
                :options="[
                    ['id' => '', 'name' => 'Toutes les dates'],
                    ['id' => 'today', 'name' => 'Aujourd\'hui'],
                    ['id' => 'week', 'name' => 'Cette semaine'],
                ]"
                option-value="id"
                option-label="name"
                id="date_filter"
                name="date_filter"
                clearable />

            <x-input
                label="Recherche"
                icon="o-magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="Nom du patient"
                clearable />
        </div>
    </x-card>

    {{-- Tableau --}}
    <x-card>
        <x-table
            :headers="[
                ['key' => 'treatment_date', 'label' => 'Date', 'sortable' => true],
                ['key' => 'patient.name', 'label' => 'Patient'],
                ['key' => 'service', 'label' => 'Service'],
                ['key' => 'care_provided', 'label' => 'Soins'],
                ['key' => 'patient_condition', 'label' => 'État'],
                ['key' => 'doctor.name', 'label' => 'Médecin'],
                {{-- ['key' => 'cost', 'label' => 'Coût'], --}}
                ['key' => 'actions', 'label' => 'Actions'],
            ]"
            :rows="$treatments"
            :sort-by="$sortBy"
            with-pagination
            striped>

            @scope('cell_treatment_date', $treatment)
                {{ \Carbon\Carbon::parse($treatment['treatment_date'])->format('d/m/Y') }}
            @endscope

            @scope('cell_service', $treatment)
                @php
                    $serviceMap = [
                        'App\\Models\\Toxicology' => 'Toxicologie',
                        'App\\Models\\Psychopathology' => 'Psychopathologie',
                        'App\\Models\\Medecine' => 'Médecine',
                    ];
                    $serviceName = $serviceMap[$treatment['treatable_type']] ?? 'Général';
                @endphp
                <x-badge :value="$serviceName" class="badge-info badge-soft" />
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

            @scope('actions', $treatment)
                <div class="flex gap-1">
                    {{-- Bouton VOIR --}}
                    <x-button icon="o-eye" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Voir les détails"
                        wire:click="viewTreatment({{ $treatment['id'] }})" />

                    {{-- Bouton MODIFIER --}}
                    <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Modifier"
                        wire:click="editTreatment({{ $treatment['id'] }})" />

                    {{-- Bouton SUPPRIMER --}}
                    <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm"
                        tooltip-left="Supprimer"
                        wire:click="confirmDelete({{ $treatment['id'] }})" />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- ==================== MODAL VISUALISATION ==================== --}}
    <x-modal wire:model="showViewModal" title="Détail du traitement" size="3xl" separator>
        @if($viewingTreatment)
            <div class="space-y-4">
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
                @php
                    $typeMap = [
                        'App\\Models\\Toxicology' => 'toxicologie',
                        'App\\Models\\Psychopathology' => 'psychopathologie',
                        'App\\Models\\Medecine' => 'medecine',
                    ];
                    $consultationType = $typeMap[$viewingTreatment['treatable_type']] ?? null;
                @endphp
                @if($consultationType && $viewingTreatment['treatable_id'])
                    <x-button label="Voir la consultation" icon="o-link" class="btn-outline"
                        link="{{ route('consultations.' . $consultationType . '.show', $viewingTreatment['treatable_id']) }}" />
                @endif
                <x-button label="Fermer" wire:click="$set('showViewModal', false)" />
            </x-slot:actions>
        @endif
    </x-modal>

    {{-- ==================== MODAL CONFIRMATION SUPPRESSION ==================== --}}
    <x-modal wire:model="showDeleteModal" title="Confirmation" separator>
        <p>Êtes-vous sûr de vouloir supprimer ce traitement ? Cette action est irréversible.</p>
        <x-slot:actions>
            <x-button label="Annuler" wire:click="$set('showDeleteModal', false)" />
            <x-button label="Supprimer" class="btn-error" wire:click="deleteTreatment" spinner="deleteTreatment" />
        </x-slot:actions>
    </x-modal>
</div>
