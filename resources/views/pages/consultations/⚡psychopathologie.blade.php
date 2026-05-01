<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Psychopathology;
use App\Models\Patient;

new
#[Title('Consultations - Psychopathologie')]
class extends Component {
    use WithPagination, WithFileUploads;

    public string $search = '';
    public ?int $patient_id = null;
    public array $sortBy = ['column' => 'consultation_date', 'direction' => 'desc'];

    // Formulaire
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $patientId = null;
    public string $consultation_date = '';

    // Anamnèse
    public string $chief_complaint = '';
    public string $illness_history = '';

    // Examen clinique
    public string $appearance = '';
    public string $contact_quality = '';
    public bool $agitation = false;
    public bool $hallucinations = false;
    public bool $suicidal_ideation = false;
    public bool $suicide_attempts = false;
    public bool $alcoholism = false;
    public bool $smoking = false;
    public string $other_addictions = '';

    // Conclusion
    public string $clinical_conclusion = '';
    public string $treatment_recommendations = '';

    // Documents
    public $documents = [];

    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    public function getConsultationsProperty()
    {
        return Psychopathology::query()
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
        $consultation = Psychopathology::findOrFail($id);
        $this->editingId = $id;
        $this->patientId = $consultation->patient_id;
        $this->consultation_date = $consultation->consultation_date;
        $this->chief_complaint = $consultation->chief_complaint;
        $this->illness_history = $consultation->illness_history;
        $this->appearance = $consultation->appearance;
        $this->contact_quality = $consultation->contact_quality;
        $this->agitation = $consultation->agitation ?? false;
        $this->hallucinations = $consultation->hallucinations ?? false;
        $this->suicidal_ideation = $consultation->suicidal_ideation ?? false;
        $this->suicide_attempts = $consultation->suicide_attempts ?? false;
        $this->alcoholism = $consultation->alcoholism ?? false;
        $this->smoking = $consultation->smoking ?? false;
        $this->other_addictions = $consultation->other_addictions;
        $this->clinical_conclusion = $consultation->clinical_conclusion;
        $this->treatment_recommendations = $consultation->treatment_recommendations;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'clinical_conclusion' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = [
            'patient_id' => $this->patientId,
            'doctor_id' => auth()->id(),
            'consultation_date' => $this->consultation_date,
            'chief_complaint' => $this->chief_complaint,
            'illness_history' => $this->illness_history,
            'appearance' => $this->appearance,
            'contact_quality' => $this->contact_quality,
            'agitation' => $this->agitation,
            'hallucinations' => $this->hallucinations,
            'suicidal_ideation' => $this->suicidal_ideation,
            'suicide_attempts' => $this->suicide_attempts,
            'alcoholism' => $this->alcoholism,
            'smoking' => $this->smoking,
            'other_addictions' => $this->other_addictions,
            'clinical_conclusion' => $this->clinical_conclusion,
            'treatment_recommendations' => $this->treatment_recommendations,
            'status' => 'completed',
        ];

        if ($this->editingId) {
            $consultation = Psychopathology::findOrFail($this->editingId);
            $consultation->update($data);
            session()->flash('success', 'Consultation modifiée avec succès.');
        } else {
            $consultation = Psychopathology::create($data);
            session()->flash('success', 'Consultation créée avec succès.');
        }

        // Gestion des fichiers
        if (!empty($this->documents)) {
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$this->patientId}/psychopathologie", 'public');
                $documents = $consultation->documents ?? [];
                $documents[] = ['path' => $path, 'original_name' => $file->getClientOriginalName()];
                $consultation->documents = $documents;
                $consultation->save();
            }
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Psychopathology::findOrFail($id)->delete();
        session()->flash('success', 'Consultation supprimée avec succès.');
    }

    public function resetForm()
    {
        $this->reset([
            'editingId', 'patientId', 'consultation_date',
            'chief_complaint', 'illness_history', 'appearance', 'contact_quality',
            'agitation', 'hallucinations', 'suicidal_ideation', 'suicide_attempts',
            'alcoholism', 'smoking', 'other_addictions',
            'clinical_conclusion', 'treatment_recommendations', 'documents'
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
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Consultations Psychopathologie</h1>
            <p class="text-base-content/70 mt-1">Évaluations psychiatriques et psychologiques</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle consultation
        </button>
    </div>

    {{-- Filtres --}}
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <select wire:model.live="patient_id" class="select select-bordered w-full">
                    <option value="">Tous les patients</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                    @endforeach
                </select>

                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="input input-bordered w-full" />
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Motif principal</th>
                        <th>Médecin</th>
                        <th>Conclusion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                    <tr class="hover">
                        <td>{{ \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y') }}</td>
                        <td class="font-medium">{{ $consultation->patient->name }}</td>
                        <td>{{ Str::limit($consultation->chief_complaint, 40) ?? '-' }}</td>
                        <td>{{ $consultation->doctor->name }}</td>
                        <td>{{ Str::limit($consultation->clinical_conclusion, 40) ?? '-' }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $consultation->id }})" class="btn btn-sm btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $consultation->id }})" wire:confirm="Supprimer cette consultation ?" class="btn btn-sm btn-ghost text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </table>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8">Aucune consultation trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-actions justify-end p-4">
            {{ $consultations->links() }}
        </div>
    </div>

    {{-- Modal Formulaire --}}
    <dialog class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-3xl">
            <h3 class="font-bold text-lg mb-4">{{ $editingId ? 'Modifier la consultation' : 'Nouvelle consultation' }}</h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Patient *</span></label>
                        <select wire:model="patientId" class="select select-bordered" required>
                            <option value="">Sélectionner un patient</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->medical_record_number }}</option>
                            @endforeach
                        </select>
                        @error('patientId') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date de consultation *</span></label>
                        <input type="date" wire:model="consultation_date" class="input input-bordered" required />
                        @error('consultation_date') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Sections --}}
                <div class="tabs tabs-boxed mb-4">
                    <a class="tab tab-active" onclick="event.preventDefault(); showTab('tab-anamnese')">Anamnèse</a>
                    <a class="tab" onclick="event.preventDefault(); showTab('tab-clinical')">Examen clinique</a>
                    <a class="tab" onclick="event.preventDefault(); showTab('tab-conclusion')">Conclusion</a>
                </div>

                <div id="tab-anamnese" class="space-y-4">
                    <textarea wire:model="chief_complaint" class="textarea textarea-bordered w-full" rows="3" placeholder="Motif principal de consultation"></textarea>
                    <textarea wire:model="illness_history" class="textarea textarea-bordered w-full" rows="5" placeholder="Histoire de la maladie"></textarea>
                </div>

                <div id="tab-clinical" class="space-y-4 hidden">
                    <textarea wire:model="appearance" class="textarea textarea-bordered w-full" rows="2" placeholder="Apparence / Présentation"></textarea>
                    <input type="text" wire:model="contact_quality" class="input input-bordered w-full" placeholder="Qualité du contact" />

                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="agitation" class="checkbox" />
                            <span>Agitation</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="hallucinations" class="checkbox" />
                            <span>Hallucinations</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="suicidal_ideation" class="checkbox" />
                            <span>Idées suicidaires</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="suicide_attempts" class="checkbox" />
                            <span>Tentatives suicide</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="alcoholism" class="checkbox" />
                            <span>Alcoolisme</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="smoking" class="checkbox" />
                            <span>Tabagisme</span>
                        </label>
                    </div>

                    <textarea wire:model="other_addictions" class="textarea textarea-bordered w-full" rows="2" placeholder="Autres addictions"></textarea>
                </div>

                <div id="tab-conclusion" class="space-y-4 hidden">
                    <textarea wire:model="clinical_conclusion" class="textarea textarea-bordered w-full" rows="4" placeholder="Conclusion clinique"></textarea>
                    <textarea wire:model="treatment_recommendations" class="textarea textarea-bordered w-full" rows="3" placeholder="Recommandations thérapeutiques"></textarea>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Documents (PDF, Images)</span></label>
                        <input type="file" wire:model="documents" multiple class="file-input file-input-bordered w-full" />
                        @error('documents.*') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action mt-6">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-ghost">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button wire:click="$set('showModal', false)">Fermer</button>
        </form>
    </dialog>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('#tab-anamnese, #tab-clinical, #tab-conclusion').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.getElementById(tabId).classList.remove('hidden');
        document.querySelectorAll('.tabs a').forEach(t => t.classList.remove('tab-active'));
        event.target.classList.add('tab-active');
    }
</script>
