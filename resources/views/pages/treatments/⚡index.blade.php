<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Treatment;
use App\Models\Patient;
use App\Models\User;

new
#[Title('Traitements quotidiens')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public ?int $patient_id = null;
    public string $date_filter = '';
    public array $sortBy = ['column' => 'treatment_date', 'direction' => 'desc'];

    // Formulaire
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $patientId = null;
    public string $treatment_date = '';
    public string $treatment_time = '';
    public string $observations = '';
    public string $medications_given = '';
    public string $care_provided = '';
    public ?float $cost = null;
    public string $patient_condition = '';
    public string $doctor_notes = '';
    public ?string $next_instructions = '';
    public string $treatable_type = '';
    public ?int $treatable_id = null;

    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    public function getTreatmentsProperty()
    {
        $query = Treatment::query()
            ->with(['patient', 'doctor', 'treatable'])
            ->when(auth()->user()->role === 'medecin', fn($q) => $q->where('doctor_id', auth()->id()))
            ->when($this->patient_id, fn($q) => $q->where('patient_id', $this->patient_id))
            ->when($this->date_filter === 'today', fn($q) => $q->whereDate('treatment_date', today()))
            ->when($this->date_filter === 'week', fn($q) => $q->whereBetween('treatment_date', [now()->startOfWeek(), now()->endOfWeek()]))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        if (auth()->user()->isAdmin()) {
            return $query->paginate(15);
        }

        return $query->paginate(15);
    }

    public function getServiceTypesProperty()
    {
        return [
            ['id' => 'toxicologie', 'name' => 'Toxicologie', 'class' => 'App\\Models\\Toxicology'],
            ['id' => 'psychopathologie', 'name' => 'Psychopathologie', 'class' => 'App\\Models\\Psychopathology'],
            ['id' => 'medecine', 'name' => 'Médecine Générale', 'class' => 'App\\Models\\Medecine'],
        ];
    }

    public function getAvailableTreatablesProperty()
    {
        if (!$this->patientId) return [];

        $treatables = [];
        $services = [
            'toxicologie' => 'App\\Models\\Toxicology',
            'psychopathologie' => 'App\\Models\\Psychopathology',
            'medecine' => 'App\\Models\\Medecine',
        ];

        foreach ($services as $key => $class) {
            $records = $class::where('patient_id', $this->patientId)->get();
            foreach ($records as $record) {
                $treatables[] = [
                    'id' => $record->id,
                    'type' => $key,
                    'label' => ucfirst($key) . ' - ' . ($record->consultation_date ?? $record->created_at->format('d/m/Y'))
                ];
            }
        }

        return $treatables;
    }

    public function create()
    {
        $this->resetForm();
        $this->treatment_date = date('Y-m-d');
        $this->treatment_time = date('H:i');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $treatment = Treatment::findOrFail($id);
        $this->editingId = $id;
        $this->patientId = $treatment->patient_id;
        $this->treatment_date = $treatment->treatment_date->format('Y-m-d');
        $this->treatment_time = $treatment->treatment_time ? \Carbon\Carbon::parse($treatment->treatment_time)->format('H:i') : '';
        $this->observations = $treatment->observations;
        $this->medications_given = $treatment->medications_given;
        $this->care_provided = $treatment->care_provided;
        $this->cost = $treatment->cost;
        $this->patient_condition = $treatment->patient_condition;
        $this->doctor_notes = $treatment->doctor_notes;
        $this->next_instructions = $treatment->next_instructions;
        $this->treatable_type = $this->getServiceTypeFromClass($treatment->treatable_type);
        $this->treatable_id = $treatment->treatable_id;
        $this->showModal = true;
    }

    private function getServiceTypeFromClass($class)
    {
        $mapping = [
            'App\\Models\\Toxicology' => 'toxicologie',
            'App\\Models\\Psychopathology' => 'psychopathologie',
            'App\\Models\\Medecine' => 'medecine',
        ];
        return $mapping[$class] ?? '';
    }

    public function save()
    {
        $this->validate([
            'patientId' => 'required|exists:patients,id',
            'treatment_date' => 'required|date',
            'observations' => 'nullable|string',
            'patient_condition' => 'nullable|string|in:stable,amélioré,dégradé',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'patient_id' => $this->patientId,
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

        if ($this->treatable_type && $this->treatable_id) {
            $classMapping = [
                'toxicologie' => 'App\\Models\\Toxicology',
                'psychopathologie' => 'App\\Models\\Psychopathology',
                'medecine' => 'App\\Models\\Medecine',
            ];
            $data['treatable_type'] = $classMapping[$this->treatable_type];
            $data['treatable_id'] = $this->treatable_id;
        }

        if ($this->editingId) {
            $treatment = Treatment::findOrFail($this->editingId);
            $treatment->update($data);
            session()->flash('success', 'Traitement modifié avec succès.');
        } else {
            Treatment::create($data);
            session()->flash('success', 'Traitement enregistré avec succès.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Treatment::findOrFail($id)->delete();
        session()->flash('success', 'Traitement supprimé avec succès.');
    }

    public function resetForm()
    {
        $this->reset([
            'editingId', 'patientId', 'treatment_date', 'treatment_time',
            'observations', 'medications_given', 'care_provided', 'cost',
            'patient_condition', 'doctor_notes', 'next_instructions',
            'treatable_type', 'treatable_id'
        ]);
        $this->resetValidation();
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
    {{-- En-tête --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Traitements quotidiens</h1>
            <p class="text-base-content/70 mt-1">Gestion des soins et traitements quotidiens</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouveau traitement
        </button>
    </div>

    {{-- Filtres --}}
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <select wire:model.live="patient_id" class="select select-bordered w-full">
                    <option value="">Tous les patients</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->medical_record_number }}</option>
                    @endforeach
                </select>

                <select wire:model.live="date_filter" class="select select-bordered w-full">
                    <option value="">Toutes les périodes</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                </select>

                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un patient..." class="input input-bordered w-full" />
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
                        <th>Soins prodigués</th>
                        <th>État</th>
                        <th>Médecin</th>
                        <th>Coût</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treatments as $treatment)
                    <tr class="hover">
                        <td>{{ \Carbon\Carbon::parse($treatment->treatment_date)->format('d/m/Y') }}
                            @if($treatment->treatment_time)
                                <br><span class="text-xs">{{ \Carbon\Carbon::parse($treatment->treatment_time)->format('H:i') }}</span>
                            @endif
                        </td>
                        <td class="font-medium">{{ $treatment->patient->name }}<br>
                            <span class="text-xs text-base-content/60">{{ $treatment->patient->medical_record_number }}</span>
                        </td>
                        <td>{{ Str::limit($treatment->care_provided ?? $treatment->observations, 40) ?? '-' }}</td>
                        <td>
                            @if($treatment->patient_condition === 'stable')
                                <span class="badge badge-info badge-sm">Stable</span>
                            @elseif($treatment->patient_condition === 'amélioré')
                                <span class="badge badge-success badge-sm">Amélioré</span>
                            @elseif($treatment->patient_condition === 'dégradé')
                                <span class="badge badge-error badge-sm">Dégradé</span>
                            @else
                                <span class="badge badge-ghost badge-sm">-</span>
                            @endif
                        </td>
                        <td>{{ $treatment->doctor->name }}</td>
                        <td>{{ $treatment->cost ? number_format($treatment->cost, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $treatment->id }})" class="btn btn-sm btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $treatment->id }})" wire:confirm="Supprimer ce traitement ?" class="btn btn-sm btn-ghost text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover">
                        <td colspan="7" class="text-center py-8">Aucun traitement enregistré</td>
                     </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-actions justify-end p-4">
            {{ $treatments->links() }}
        </div>
    </div>

    {{-- Modal Formulaire --}}
    <dialog class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-3xl">
            <h3 class="font-bold text-lg mb-4">{{ $editingId ? 'Modifier le traitement' : 'Nouveau traitement' }}</h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Patient *</span></label>
                        <select wire:model.live="patientId" class="select select-bordered" required>
                            <option value="">Sélectionner un patient</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->medical_record_number }}</option>
                            @endforeach
                        </select>
                        @error('patientId') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Consultation liée</span></label>
                        <select wire:model="treatable_id" class="select select-bordered"
                            @if(!$patientId) disabled @endif>
                            <option value="">Aucune</option>
                            @foreach($this->availableTreatables as $treatable)
                                <option value="{{ $treatable['id'] }}" data-type="{{ $treatable['type'] }}">
                                    {{ $treatable['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" wire:model="treatable_type" />
                    </div>

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
                    <a class="tab tab-active" onclick="event.preventDefault(); showTab('tab-care')">Soins</a>
                    <a class="tab" onclick="event.preventDefault(); showTab('tab-evaluation')">Évaluation</a>
                    <a class="tab" onclick="event.preventDefault(); showTab('tab-financial')">Finances</a>
                </div>

                <div id="tab-care" class="space-y-4">
                    <textarea wire:model="observations" class="textarea textarea-bordered w-full" rows="3" placeholder="Observations du jour"></textarea>
                    <textarea wire:model="care_provided" class="textarea textarea-bordered w-full" rows="3" placeholder="Soins prodigués"></textarea>
                    <textarea wire:model="medications_given" class="textarea textarea-bordered w-full" rows="2" placeholder="Médicaments administrés"></textarea>
                </div>

                <div id="tab-evaluation" class="space-y-4 hidden">
                    <select wire:model="patient_condition" class="select select-bordered w-full">
                        <option value="">État du patient</option>
                        <option value="stable">Stable</option>
                        <option value="amélioré">Amélioré</option>
                        <option value="dégradé">Dégradé</option>
                    </select>

                    <textarea wire:model="doctor_notes" class="textarea textarea-bordered w-full" rows="3" placeholder="Notes du médecin"></textarea>
                    <textarea wire:model="next_instructions" class="textarea textarea-bordered w-full" rows="2" placeholder="Instructions pour prochain traitement"></textarea>
                </div>

                <div id="tab-financial" class="space-y-4 hidden">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Coût (FCFA)</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60">FCFA</span>
                            <input type="number" wire:model="cost" class="input input-bordered w-full pl-16" step="100" min="0" placeholder="0" />
                        </div>
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
        document.querySelectorAll('#tab-care, #tab-evaluation, #tab-financial').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.getElementById(tabId).classList.remove('hidden');
        document.querySelectorAll('.tabs a').forEach(t => t.classList.remove('tab-active'));
        event.target.classList.add('tab-active');
    }

    // Mise à jour du type de consultation lors de la sélection
    document.addEventListener('livewire:initialized', () => {
        const treatableSelect = document.querySelector('[wire\\:model="treatable_id"]');
        if (treatableSelect) {
            treatableSelect.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const treatableType = selectedOption?.dataset?.type;
                if (treatableType && window.Livewire) {
                    window.Livewire.find('{{ $this->getId() }}').set('treatable_type', treatableType);
                }
            });
        }
    });
</script>
