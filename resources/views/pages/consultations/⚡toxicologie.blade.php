<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Toxicology;
use App\Models\Patient;
use App\Models\User;

new
#[Title('Consultations - Toxicologie')]
class extends Component {
    use WithPagination, WithFileUploads;

    public string $search = '';
    public ?int $patient_id = null;
    public array $sortBy = ['column' => 'consultation_date', 'direction' => 'desc'];

    // Formulaire
    public bool $showModal = false;
    public ?int $editingId = null;

    // Histoire des conduites addictives
    public ?int $patientId = null;
    public string $consultation_date = '';
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

    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
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
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'consultation_date' => 'required|date',
            'substances_used' => 'nullable|string',
            'diagnostic_conclusion' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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
            session()->flash('success', 'Consultation modifiée avec succès.');
        } else {
            $consultation = Toxicology::create($data);
            session()->flash('success', 'Consultation créée avec succès.');
        }

        // Gestion des fichiers
        if (!empty($this->documents)) {
            foreach ($this->documents as $file) {
                $path = $file->store("patients/{$this->patientId}/toxicologie", 'public');
                $consultation->documents = array_merge($consultation->documents ?? [], [
                    ['path' => $path, 'original_name' => $file->getClientOriginalName()]
                ]);
                $consultation->save();
            }
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Toxicology::findOrFail($id)->delete();
        session()->flash('success', 'Consultation supprimée avec succès.');
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
            'diagnostic_conclusion', 'treatment_plan', 'recommendations', 'documents'
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
            <h1 class="text-3xl font-bold">Consultations Toxicologie</h1>
            <p class="text-base-content/70 mt-1">Gestion des évaluations en addictologie</p>
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
                        <th>Substances</th>
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
                        <td>{{ Str::limit($consultation->substances_used, 30) ?? '-' }}</td>
                        <td>{{ $consultation->doctor->name }}</td>
                        <td>{{ Str::limit($consultation->diagnostic_conclusion, 30) ?? '-' }}</td>
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
                    </tr>
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
        <div class="modal-box w-11/12 max-w-4xl">
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
                    <a class="tab tab-active" onclick="event.preventDefault(); document.getElementById('tab-history').click()">Histoire addictive</a>
                    <a class="tab" onclick="event.preventDefault(); document.getElementById('tab-consequences').click()">Conséquences</a>
                    <a class="tab" onclick="event.preventDefault(); document.getElementById('tab-conclusion').click()">Conclusion</a>
                </div>

                {{-- Onglet Histoire --}}
                <div id="tab-history" class="space-y-4">
                    <textarea wire:model="substances_used" class="textarea textarea-bordered w-full" rows="2" placeholder="Substances consommées"></textarea>
                    <input type="number" wire:model="substances_start_age" class="input input-bordered w-full" placeholder="Âge de début" />
                    <textarea wire:model="substances_start_reason" class="textarea textarea-bordered w-full" rows="2" placeholder="Raison du début"></textarea>
                    <textarea wire:model="current_consumption_motivation" class="textarea textarea-bordered w-full" rows="2" placeholder="Motivation actuelle"></textarea>
                    <textarea wire:model="tolerance_description" class="textarea textarea-bordered w-full" rows="2" placeholder="Description de la tolérance"></textarea>
                    <textarea wire:model="withdrawal_attempts" class="textarea textarea-bordered w-full" rows="2" placeholder="Tentatives d'arrêt"></textarea>
                    <textarea wire:model="stop_motivation" class="textarea textarea-bordered w-full" rows="2" placeholder="Motivation à arrêter"></textarea>
                    <input type="text" wire:model="max_abstinence_duration" class="input input-bordered w-full" placeholder="Durée max d'abstinence" />
                </div>

                {{-- Onglet Conséquences (caché par défaut) --}}
                <div id="tab-consequences" class="space-y-4 hidden">
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="weight_loss" class="checkbox" />
                            <span>Amaigrissement</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="pale_complexion" class="checkbox" />
                            <span>Teint sombre</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="withdrawal_insomnia" class="checkbox" />
                            <span>Insomnie de manque</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="nightmares" class="checkbox" />
                            <span>Cauchemars</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="hallucinations" class="checkbox" />
                            <span>Hallucinations</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="somatic_disorders" class="checkbox" />
                            <span>Troubles somatiques</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="behavioral_delirium" class="checkbox" />
                            <span>Délire/Trouble comportement</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="legal_issues" class="checkbox" />
                            <span>Problèmes judiciaires</span>
                        </label>
                    </div>
                </div>

                {{-- Onglet Conclusion --}}
                <div id="tab-conclusion" class="space-y-4 hidden">
                    <textarea wire:model="diagnostic_conclusion" class="textarea textarea-bordered w-full" rows="3" placeholder="Conclusion diagnostique"></textarea>
                    <textarea wire:model="treatment_plan" class="textarea textarea-bordered w-full" rows="3" placeholder="Plan de traitement"></textarea>
                    <textarea wire:model="recommendations" class="textarea textarea-bordered w-full" rows="2" placeholder="Recommandations"></textarea>

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
    // Gestion des onglets
    document.querySelectorAll('.tabs a').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            document.querySelectorAll('.tabs a').forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');
            document.getElementById('tab-history').classList.add('hidden');
            document.getElementById('tab-consequences').classList.add('hidden');
            document.getElementById('tab-conclusion').classList.add('hidden');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });
</script>
