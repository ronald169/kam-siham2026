<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Treatment;
use App\Models\Patient;

new
#[Title('Modifier traitement')]
class extends Component {
    use Toast;

    public ?int $consultation_id = null;
    public ?string $consultation_type = null;
    public ?int $treatment_id = null;
    public bool $modal = false;

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
    public string $activeTab = 'care';

    public function mount($id, $consultation_id = null, $type = null)
    {
        $this->treatment_id = $id;
        $this->consultation_id = $consultation_id;
        $this->consultation_type = $type;
        $this->loadTreatment();
        $this->modal = true;
    }

    public function loadTreatment()
    {
        $treatment = Treatment::findOrFail($this->treatment_id);
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
    }

    public function update()
    {
        $this->validate([
            'treatment_date' => 'required|date',
            'patient_condition' => 'nullable|string|in:stable,amélioré,dégradé',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $treatment = Treatment::findOrFail($this->treatment_id);
        $treatment->update([
            'treatment_date' => $this->treatment_date,
            'treatment_time' => $this->treatment_time ?: null,
            'observations' => $this->observations,
            'medications_given' => $this->medications_given,
            'care_provided' => $this->care_provided,
            'cost' => $this->cost,
            'patient_condition' => $this->patient_condition,
            'doctor_notes' => $this->doctor_notes,
            'next_instructions' => $this->next_instructions,
        ]);

        $this->dispatch('treatment-updated');
        $this->success('Traitement modifié avec succès.');

        $this->modal = false;
        $this->redirect(route('consultations.' . $this->consultation_type . '.show', $this->consultation_id));
    }

    public function render()
    {
        return $this->view();
    }
};

?>
