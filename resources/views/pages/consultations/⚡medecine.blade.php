<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Models\Medecine;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;

new
#[Title('Consultations - Médecine Générale')]
class extends Component {
    use WithPagination, WithFileUploads, Toast;

    public string $search = '';
    public ?int $patient_id = null;
    public array $sortBy = ['column' => 'consultation_date', 'direction' => 'desc'];

    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $patientId = null;
    public string $consultation_date = '';
    public string $consultation_reason = '';
    public string $illness_history = '';
    public string $medical_history = '';
    public string $physical_examination = '';
    public string $diagnostic_hypothesis = '';
    public string $requested_exams = '';
    public string $prescribed_treatments = '';
    public string $medical_prescriptions = '';
    public ?string $next_appointment = null;
    public string $follow_up_instructions = '';
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
        return Medecine::query()
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
        $consultation = Medecine::findOrFail($id);
        $this->editingId = $id;
        $this->patientId = $consultation->patient_id;
        $this->consultation_date = $consultation->consultation_date;
        $this->consultation_reason = $consultation->consultation_reason;
        $this->illness_history = $consultation->illness_history;
        $this->medical_history = $consultation->medical_history;
        $this->physical_examination = $consultation->physical_examination;
        $this->diagnostic_hypothesis = $consultation->diagnostic_hypothesis;
        $this->requested_exams = $consultation->requested_exams;
        $this->prescribed_treatments = $consultation->prescribed_treatments;
        $this->medical_prescriptions = $consultation->medical_prescriptions;
        $this->next_appointment = $consultation->next_appointment;
        $this->follow_up_instructions = $consultation->follow_up_instructions;
        $this->existingDocuments = $consultation->documents ?? [];
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'consultation_reason' => 'nullable|string',
            'diagnostic_hypothesis' => 'nullable|string',
            'prescribed_treatments' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'consultation_reason' => $this->consultation_reason,
            'illness_history' => $this->illness_history,
            'medical_history' => $this->medical_history,
            'physical_examination' => $this->physical_examination,
            'diagnostic_hypothesis' => $this->diagnostic_hypothesis,
            'requested_exams' => $this->requested_exams,
            'prescribed_treatments' => $this->prescribed_treatments,
            'medical_prescriptions' => $this->medical_prescriptions,
            'next_appointment' => $this->next_appointment,
            'follow_up_instructions' => $this->follow_up_instructions,
            'status' => 'completed',
        ];

        if ($this->editingId) {
            $consultation = Medecine::findOrFail($this->editingId);
            $consultation->update($data);
            $this->success('Consultation modifiée.');
        } else {
            $consultation = Medecine::create($data);
            $this->success('Consultation créée.');
        }

        if (!empty($this->documents)) {
            $documents = $consultation->documents ?? [];
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$this->patientId}/medecine", 'public');
                $documents[] = ['path' => $path, 'original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()];
            }
            $consultation->documents = $documents;
            $consultation->save();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deleteDocument($index)
    {
        $consultation = Medecine::findOrFail($this->editingId);
        $documents = $consultation->documents ?? [];
        if (isset($documents[$index])) {
            \Storage::disk('public')->delete($documents[$index]['path']);
            array_splice($documents, $index, 1);
            $consultation->documents = $documents;
            $consultation->save();
            $this->existingDocuments = $documents;
            $this->success('Document supprimé.');
        }
    }

    public function downloadDocument($index)
    {
        $consultation = Medecine::findOrFail($this->editingId);
        $documents = $consultation->documents ?? [];
        if (isset($documents[$index])) {
            return response()->download(storage_path('app/public/' . $documents[$index]['path']), $documents[$index]['original_name']);
        }
    }

    public function downloadPdf($id)
    {
        $consultation = Medecine::with(['patient', 'doctor'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.medecine', ['consultation' => $consultation]);
        return response()->streamDownload(fn () => print($pdf->output()), 'medecine_' . $consultation->patient->medical_record_number . '.pdf');
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

    public function resetForm()
    {
        $this->reset(['editingId', 'patientId', 'consultation_date', 'consultation_reason', 'illness_history', 'medical_history', 'physical_examination', 'diagnostic_hypothesis', 'requested_exams', 'prescribed_treatments', 'medical_prescriptions', 'next_appointment', 'follow_up_instructions', 'documents', 'existingDocuments']);
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
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div><h1 class="text-3xl font-bold">Médecine Générale</h1><p class="text-base-content/70">Consultations médicales généralistes</p></div>
        <x-button label="Nouvelle consultation" icon="o-plus" class="btn-primary" wire:click="create" />
    </div>

    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-select label="Patient" wire:model.live="patient_id" :options="$patients" option-value="id" option-label="name" placeholder="Tous" id="patient_id" name="patient_id" clearable />
            <x-input label="Recherche" icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" clearable />
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[['key' => 'consultation_date', 'label' => 'Date'], ['key' => 'patient.name', 'label' => 'Patient'], ['key' => 'consultation_reason', 'label' => 'Motif'], ['key' => 'diagnostic_hypothesis', 'label' => 'Diagnostic']]" :rows="$consultations" with-pagination striped>
            @scope('cell_consultation_date', $consultation){{ \Carbon\Carbon::parse($consultation['consultation_date'])->format('d/m/Y') }}@endscope
            @scope('actions', $consultation)
                <div class="flex gap-1">
                    <x-button icon="o-arrow-down-tray" class="btn-circle btn-ghost btn-sm" tooltip-left="PDF" wire:click="downloadPdf({{ $consultation['id'] }})" spinner />
                    <x-button icon="o-pencil" class="btn-circle btn-ghost btn-sm" wire:click="edit({{ $consultation['id'] }})" />
                    <x-button icon="o-trash" class="btn-circle btn-ghost btn-sm" wire:click="delete({{ $consultation['id'] }})" wire:confirm="Supprimer ?" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="showModal" title="{{ $editingId ? 'Modifier' : 'Nouvelle consultation' }}" size="3xl" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <x-select label="Patient" wire:model="patientId" :options="$patients" option-value="id" option-label="name" required id="patientId" name="patientId" />
                <x-datepicker label="Date" wire:model="consultation_date" required id="consultation_date" name="consultation_date" />
            </div>
            <div class="tabs tabs-boxed mb-4">
                <a class="tab tab-active" onclick="event.preventDefault(); showTab('tab-clinical')">Clinique</a>
                <a class="tab" onclick="event.preventDefault(); showTab('tab-treatment')">Traitement</a>
                <a class="tab" onclick="event.preventDefault(); showTab('tab-documents')">Documents</a>
            </div>
            <div id="tab-clinical" class="space-y-4">
                <x-textarea label="Motif" wire:model="consultation_reason" rows="2" />
                <x-textarea label="Histoire maladie" wire:model="illness_history" rows="3" />
                <x-textarea label="Antécédents" wire:model="medical_history" rows="2" />
                <x-textarea label="Examen physique" wire:model="physical_examination" rows="3" />
                <x-textarea label="Diagnostic" wire:model="diagnostic_hypothesis" rows="2" />
            </div>
            <div id="tab-treatment" class="space-y-4 hidden">
                <x-textarea label="Examens" wire:model="requested_exams" rows="2" />
                <x-textarea label="Traitements" wire:model="prescribed_treatments" rows="3" />
                <x-textarea label="Ordonnance" wire:model="medical_prescriptions" rows="3" />
                <x-datepicker label="Prochain RDV" wire:model="next_appointment" />
                <x-textarea label="Instructions" wire:model="follow_up_instructions" rows="2" />
            </div>
            <div id="tab-documents" class="space-y-4 hidden">
                @if(count($existingDocuments) > 0)
                    @foreach($existingDocuments as $index => $doc)
                        <div class="flex justify-between items-center p-2 bg-base-200 rounded">
                            <span>{{ $doc['original_name'] }}</span>
                            <div class="flex gap-1">
                                <x-button icon="o-arrow-down-tray" class="btn-xs btn-ghost" wire:click="downloadDocument({{ $index }})" />
                                <x-button icon="o-trash" class="btn-xs btn-ghost text-error" wire:click="deleteDocument({{ $index }})" />
                            </div>
                        </div>
                    @endforeach
                @endif
                <x-file label="Documents" wire:model="documents" multiple accept="pdf,jpg,jpeg,png" />
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
        document.querySelectorAll('#tab-clinical, #tab-treatment, #tab-documents').forEach(t => t.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');
        document.querySelectorAll('.tabs a').forEach(t => t.classList.remove('tab-active'));
        event.target.classList.add('tab-active');
    }
</script>
