<?php

use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Treatment;
use App\Models\Toxicology;

new class extends Component {
    use WithPagination, Toast;

    public Toxicology $consultation;
    public array $sortBy = ['column' => 'treatment_date', 'direction' => 'desc'];

    public function mount($consultationId)
    {
        $this->consultation = Toxicology::findOrFail($consultationId);
    }

    public function getTreatmentsProperty()
    {
        return Treatment::where('treatable_type', 'App\\Models\\Toxicology')
            ->where('treatable_id', $this->consultation->id)
            ->with('doctor')
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);
    }

    public function delete($id)
    {
        Treatment::findOrFail($id)->delete();
        $this->success('Traitement supprimé');
        $this->dispatch('treatment-deleted');
    }

    public function render()
    {
        return $this->view([
            'treatments' => $this->treatments,
        ]);
    }
};

?>

<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Historique des traitements</h3>
        <x-button label="Ajouter" icon="o-plus" class="btn-sm btn-primary"
            link="{{ route('treatments.create', ['consultation_id' => $consultation->id, 'type' => 'toxicologie']) }}" />
    </div>

    @if($treatments->count() > 0)
        <x-table
            :headers="[
                ['key' => 'treatment_date', 'label' => 'Date'],
                ['key' => 'treatment_time', 'label' => 'Heure'],
                ['key' => 'care_provided', 'label' => 'Soins'],
                ['key' => 'patient_condition', 'label' => 'État'],
                ['key' => 'doctor.name', 'label' => 'Médecin'],
            ]"
            :rows="$treatments"
            :sort-by="$sortBy"
            with-pagination
            striped>

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
                <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm"
                    wire:click="delete({{ $treatment['id'] }})"
                    wire:confirm="Supprimer ce traitement ?" />
            @endscope
        </x-table>
    @else
        <div class="text-center py-8 text-base-content/60">
            <x-icon name="o-document-check" class="h-12 w-12 mx-auto mb-2 opacity-30" />
            <p>Aucun traitement enregistré pour cette consultation</p>
            <x-button label="Ajouter un traitement" icon="o-plus" class="btn-sm btn-outline mt-3"
                link="{{ route('treatments.create', ['consultation_id' => $consultation->id, 'type' => 'toxicologie']) }}" />
        </div>
    @endif
</div>
