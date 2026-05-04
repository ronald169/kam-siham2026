<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Medecine;
use App\Models\Patient;

new
#[Title('Consultations - Médecine Générale')]
class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public ?int $patient_id = null;
    public array $sortBy = ['column' => 'consultation_date', 'direction' => 'desc'];

    public function getPatientsProperty()
    {
        return Patient::orderBy('name')->get(['id', 'name', 'medical_record_number']);
    }

    public function getConsultationsProperty()
    {
        return Medecine::query()
            ->with(['patient', 'doctor'])
            ->when($this->patient_id, fn($q) => $q->where('patient_id', $this->patient_id))
            ->when($this->search, fn($q) => $q->whereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);
    }

    public function delete($id)
    {
        $consultation = Medecine::findOrFail($id);
        if ($consultation->documents) {
            foreach ($consultation->documents as $doc) {
                \Storage::disk('public')->delete($doc['path']);
            }
        }
        $consultation->delete();
        $this->success('Consultation supprimée.');
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
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Consultations Médecine Générale</h1>
            <p class="text-base-content/70">Consultations médicales généralistes</p>
        </div>
        <x-button label="Nouvelle consultation" icon="o-plus" class="btn-primary" link="{{ route('consultations.medecine.create') }}" />
    </div>

    {{-- Filtres --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        <x-table :headers="[
            ['key' => 'consultation_date', 'label' => 'Date'],
            ['key' => 'patient.name', 'label' => 'Patient'],
            ['key' => 'patient.medical_record_number', 'label' => 'N° Dossier'],
            ['key' => 'consultation_reason', 'label' => 'Motif'],
            ['key' => 'diagnostic_hypothesis', 'label' => 'Diagnostic'],
            ['key' => 'doctor.name', 'label' => 'Médecin'],
        ]" :rows="$consultations" :sort-by="$sortBy" with-pagination striped link="/consultations/medecine/{id}/show">

            @scope('cell_consultation_date', $consultation)
                {{ \Carbon\Carbon::parse($consultation['consultation_date'])->format('d/m/Y') }}
            @endscope

            @scope('cell_consultation_reason', $consultation)
                {{ \Illuminate\Support\Str::limit($consultation['consultation_reason'], 50) ?? '-' }}
            @endscope

            @scope('cell_diagnostic_hypothesis', $consultation)
                {{ \Illuminate\Support\Str::limit($consultation['diagnostic_hypothesis'], 50) ?? '-' }}
            @endscope

            @scope('actions', $consultation)
                <div class="flex gap-1">
                    <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm" tooltip-left="Modifier"
                        link="{{ route('consultations.medecine.edit', $consultation['id']) }}" />
                    <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm" tooltip-left="Supprimer"
                        wire:click="delete({{ $consultation['id'] }})" wire:confirm="Supprimer cette consultation ?" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
