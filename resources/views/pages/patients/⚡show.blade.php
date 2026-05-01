<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;

new
#[Title('Fiche patient')]
class extends Component {
    use Toast;

    public Patient $patient;
    public string $activeTab = 'info';

    public function mount($id)
    {
        $this->patient = Patient::with([
            'referringDoctor',
            'creator',
            'toxicologies',
            'psychopathologies',
            'medecines',
            'treatments'
        ])->findOrFail($id);
    }

    public function getCanEditProperty()
    {
        return auth()->user()->isAdmin() || auth()->user()->isMedecin();
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('pdf.patient', ['patient' => $this->patient]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'patient_' . $this->patient->medical_record_number . '.pdf'
        );
    }

    public function render()
    {
        return $this->view()
            ->title($this->patient->name);
    }
};

?>

<div>
    {{-- En-tête patient --}}
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="avatar placeholder">
                <div class="bg-primary text-primary-content rounded-full w-16">
                    <span class="text-2xl font-bold">{{ substr($patient->name, 0, 1) }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-bold">{{ $patient->name }}</h1>
                <div class="flex flex-wrap gap-3 mt-1 text-base-content/70">
                    <div><x-icon name="o-identification" class="h-4 w-4 inline mr-1" />{{ $patient->medical_record_number }}</div>
                    <div><x-icon name="o-cake" class="h-4 w-4 inline mr-1" />{{ $patient->age ?? 'N/A' }} ans</div>
                    <div><x-icon name="o-calendar" class="h-4 w-4 inline mr-1" />Admis le {{ $patient->admission_date ? $patient->admission_date->format('d/m/Y') : '-' }}</div>
                    <div>
                        @if($patient->status === 'active')
                            <x-badge value="Actif" class="badge-success badge-sm" />
                        @elseif($patient->status === 'discharged')
                            <x-badge value="Sorti" class="badge-info badge-sm" />
                        @else
                            <x-badge value="Transféré" class="badge-warning badge-sm" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            @if($this->canEdit)
                <x-button label="Modifier" icon="o-pencil" class="btn-outline" link="{{ route('patients.edit', $patient->id) }}" />
            @endif
            <x-button label="Télécharger PDF" icon="o-arrow-down-tray" class="btn-primary" wire:click="downloadPdf" spinner />
        </div>
    </div>

    {{-- Onglets --}}
    <div class="tabs tabs-boxed mb-6 overflow-x-auto">
        <a class="tab {{ $activeTab === 'info' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'info')">
            <x-icon name="o-information-circle" class="h-5 w-5 mr-2" /> Informations
        </a>
        <a class="tab {{ $activeTab === 'family' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'family')">
            <x-icon name="o-home" class="h-5 w-5 mr-2" /> Famille
        </a>
        <a class="tab {{ $activeTab === 'social' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'social')">
            <x-icon name="o-user-group" class="h-5 w-5 mr-2" /> Social
        </a>
        <a class="tab {{ $activeTab === 'medical' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'medical')">
            <x-icon name="o-heart" class="h-5 w-5 mr-2" /> Médical
        </a>
        <a class="tab {{ $activeTab === 'consultations' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'consultations')">
            <x-icon name="o-clipboard-document-list" class="h-5 w-5 mr-2" /> Consultations
        </a>
        <a class="tab {{ $activeTab === 'treatments' ? 'tab-active' : '' }}" wire:click="$set('activeTab', 'treatments')">
            <x-icon name="o-document-check" class="h-5 w-5 mr-2" /> Traitements
        </a>
    </div>

    {{-- ==================== ONGLET INFORMATIONS ==================== --}}
    <div class="{{ $activeTab === 'info' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Identité" icon="o-user" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nom complet :</span><span>{{ $patient->name }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Date de naissance :</span><span>{{ $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Âge :</span><span>{{ $patient->age ?? '-' }} ans</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Sexe :</span><span>{{ $patient->sex ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Lieu de naissance :</span><span>{{ $patient->place_of_birth ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nationalité :</span><span>{{ $patient->nationality ?? 'Camerounaise' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Tribu :</span><span>{{ $patient->tribe ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Religion :</span><span>{{ $patient->religion ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Profession :</span><span>{{ $patient->profession ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Niveau d'étude :</span><span>{{ $patient->study_level ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Situation matrimoniale :</span><span>{{ $patient->marital_status ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Adresse :</span><span>{{ $patient->patient_address ?? '-' }}</span></div>
                </div>
            </x-card>

            <x-card title="Contacts" icon="o-phone" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Téléphone patient :</span><span>{{ $patient->patient_phone ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Email :</span><span>{{ $patient->patient_email ?? '-' }}</span></div>
                    <div class="divider my-2">Contact d'urgence</div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nom :</span><span>{{ $patient->emergency_contact_name ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Téléphone :</span><span>{{ $patient->emergency_contact_phone ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Lien de parenté :</span><span>{{ $patient->emergency_contact_relation ?? '-' }}</span></div>
                </div>
            </x-card>

            <x-card title="Antécédents personnels" icon="o-document-text" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Antécédents médicaux :</span><span>{{ $patient->medical_antecedents ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Antécédents chirurgicaux :</span><span>{{ $patient->surgical_antecedents ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Antécédents familiaux :</span><span>{{ $patient->family_conflicts ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Problèmes de santé :</span><span>{{ $patient->health_problems ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Allergies :</span><span>{{ $patient->allergies ?? 'Aucune' }}</span></div>
                </div>
            </x-card>

            <x-card title="Informations dossier" icon="o-folder" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">N° Dossier :</span><span class="font-mono">{{ $patient->medical_record_number }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Date d'admission :</span><span>{{ $patient->admission_date ? $patient->admission_date->format('d/m/Y') : '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Médecin référent :</span><span>{{ $patient->referringDoctor->name ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Créé par :</span><span>{{ $patient->creator->name ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Date de création :</span><span>{{ $patient->created_at ? $patient->created_at->format('d/m/Y H:i') : '-' }}</span></div>
                    @if($patient->discharge_date)
                        <div class="flex justify-between border-b pb-2"><span class="font-medium">Date de sortie :</span><span>{{ $patient->discharge_date->format('d/m/Y') }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="font-medium">Motif de sortie :</span><span>{{ $patient->discharge_reason ?? '-' }}</span></div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET FAMILLE ==================== --}}
    <div class="{{ $activeTab === 'family' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Dynamique familiale" icon="o-home" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Type de famille :</span><span>{{ $patient->type_of_family ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Rang de l'enfant :</span><span>{{ $patient->order_child ?? '-' }}</span></div>
                    <div><span class="font-medium">Dynamique familiale :</span><p class="mt-1 text-sm">{{ $patient->family_dynamics ?? 'Non renseigné' }}</p></div>
                </div>
            </x-card>

            <x-card title="Parents" icon="o-user-group" separator>
                <div class="space-y-3">
                    <div class="font-semibold text-primary">Père</div>
                    <div class="grid grid-cols-2 gap-2 pl-2">
                        <span class="text-sm">Nom :</span><span>{{ $patient->father_name ?? '-' }}</span>
                        <span class="text-sm">Âge :</span><span>{{ $patient->father_age ?? '-' }}</span>
                        <span class="text-sm">Profession :</span><span>{{ $patient->father_profession ?? '-' }}</span>
                        <span class="text-sm">Santé :</span><span>{{ $patient->father_health_status ?? '-' }}</span>
                        <span class="text-sm">Niveau d'étude :</span><span>{{ $patient->father_education_level ?? '-' }}</span>
                        <span class="text-sm">Vivant :</span><span>{{ $patient->father_alive ? 'Oui' : 'Non' }}</span>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="font-semibold text-primary">Mère</div>
                    <div class="grid grid-cols-2 gap-2 pl-2">
                        <span class="text-sm">Nom :</span><span>{{ $patient->mother_name ?? '-' }}</span>
                        <span class="text-sm">Âge :</span><span>{{ $patient->mother_age ?? '-' }}</span>
                        <span class="text-sm">Profession :</span><span>{{ $patient->mother_profession ?? '-' }}</span>
                        <span class="text-sm">Santé :</span><span>{{ $patient->mother_health_status ?? '-' }}</span>
                        <span class="text-sm">Niveau d'étude :</span><span>{{ $patient->mother_education_level ?? '-' }}</span>
                        <span class="text-sm">Vivante :</span><span>{{ $patient->mother_alive ? 'Oui' : 'Non' }}</span>
                    </div>
                </div>
            </x-card>

            <x-card title="Relations familiales" icon="o-heart" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Relation entre parents :</span><span>{{ $patient->parents_relationship ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Relation entre frères/sœurs :</span><span>{{ $patient->siblings_relationship ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Relation privilégiée :</span><span>{{ $patient->privileged_relationship ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Fréquence de retour chez les parents :</span><span>{{ $patient->frequency_stay_with_parents ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nombre d'enfants à domicile :</span><span>{{ $patient->number_of_children_at_home ?? '-' }}</span></div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET SOCIAL ==================== --}}
    <div class="{{ $activeTab === 'social' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Relations sociales" icon="o-user-group" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nombre d'amis :</span><span>{{ $patient->number_of_friends ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Vrais amis :</span><span>{{ $patient->number_of_true_friends ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Qualité des relations intimes :</span><span>{{ $patient->intimate_friends_quality ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Nature des relations :</span><span>{{ $patient->social_relations_nature ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Loisirs :</span><span>{{ $patient->leisure_activities ?? '-' }}</span></div>
                </div>
            </x-card>

            <x-card title="Psychologie" icon="o-brain-circuit" separator>
                <div class="space-y-3">
                    <div><span class="font-medium">Relation avec soi-même :</span><p class="mt-1 text-sm">{{ $patient->self_relationship ?? '-' }}</p></div>
                    <div><span class="font-medium">Problèmes perçus :</span><p class="mt-1 text-sm">{{ $patient->self_perceived_problems ?? '-' }}</p></div>
                    <div><span class="font-medium">Jugement sur soi :</span><p class="mt-1 text-sm">{{ $patient->self_judgment ?? '-' }}</p></div>
                    <div><span class="font-medium">Attentes du psychologue :</span><p class="mt-1 text-sm">{{ $patient->expectations_from_psychologist ?? '-' }}</p></div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET MÉDICAL ==================== --}}
    <div class="{{ $activeTab === 'medical' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Paramètres médicaux" icon="o-heart" separator>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Groupe sanguin :</span><span>{{ $patient->blood_type ?? '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Poids :</span><span>{{ $patient->weight ? $patient->weight . ' kg' : '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">Taille :</span><span>{{ $patient->height ? $patient->height . ' cm' : '-' }}</span></div>
                    <div class="flex justify-between border-b pb-2"><span class="font-medium">IMC :</span><span>{{ $patient->weight && $patient->height ? round($patient->weight / pow($patient->height/100, 2), 1) : '-' }}</span></div>
                </div>
            </x-card>

            <x-card title="Traitements" icon="o-document-check" separator>
                <div class="space-y-3">
                    <div><span class="font-medium">Traitements en cours :</span><p class="mt-1 text-sm">{{ $patient->current_treatments ?? 'Aucun' }}</p></div>
                    <div><span class="font-medium">Allergies :</span><p class="mt-1 text-sm">{{ $patient->allergies ?? 'Aucune' }}</p></div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET CONSULTATIONS ==================== --}}
    <div class="{{ $activeTab === 'consultations' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 gap-6">
            {{-- Toxicologie --}}
            <x-card title="Toxicologie" icon="o-beaker" separator>
                @if($patient->toxicologies->count() > 0)
                    <x-table :headers="[
                        ['key' => 'consultation_date', 'label' => 'Date'],
                        ['key' => 'substances_used', 'label' => 'Substances'],
                        ['key' => 'diagnostic_conclusion', 'label' => 'Conclusion'],
                        ['key' => 'doctor.name', 'label' => 'Médecin'],
                    ]" :rows="$patient->toxicologies">
                        @scope('cell_consultation_date', $consultation)
                            {{ \Carbon\Carbon::parse($consultation['consultation_date'])->format('d/m/Y') }}
                        @endscope
                        @scope('cell_substances_used', $consultation)
                            {{ Str::limit($consultation['substances_used'], 50) ?? '-' }}
                        @endscope
                    </x-table>
                @else
                    <div class="text-center py-8 text-base-content/60">Aucune consultation en toxicologie</div>
                @endif
            </x-card>

            {{-- Psychopathologie --}}
            <x-card title="Psychopathologie" icon="o-document-text" separator>
                @if($patient->psychopathologies->count() > 0)
                    <x-table :headers="[
                        ['key' => 'consultation_date', 'label' => 'Date'],
                        ['key' => 'chief_complaint', 'label' => 'Motif'],
                        ['key' => 'clinical_conclusion', 'label' => 'Conclusion'],
                        ['key' => 'doctor.name', 'label' => 'Médecin'],
                    ]" :rows="$patient->psychopathologies">
                        @scope('cell_consultation_date', $consultation)
                            {{ \Carbon\Carbon::parse($consultation['consultation_date'])->format('d/m/Y') }}
                        @endscope
                    </x-table>
                @else
                    <div class="text-center py-8 text-base-content/60">Aucune consultation en psychopathologie</div>
                @endif
            </x-card>

            {{-- Médecine Générale --}}
            <x-card title="Médecine Générale" icon="o-stethoscope" separator>
                @if($patient->medecines->count() > 0)
                    <x-table :headers="[
                        ['key' => 'consultation_date', 'label' => 'Date'],
                        ['key' => 'consultation_reason', 'label' => 'Motif'],
                        ['key' => 'diagnostic_hypothesis', 'label' => 'Diagnostic'],
                        ['key' => 'doctor.name', 'label' => 'Médecin'],
                    ]" :rows="$patient->medecines">
                        @scope('cell_consultation_date', $consultation)
                            {{ \Carbon\Carbon::parse($consultation['consultation_date'])->format('d/m/Y') }}
                        @endscope
                    </x-table>
                @else
                    <div class="text-center py-8 text-base-content/60">Aucune consultation en médecine générale</div>
                @endif
            </x-card>
        </div>
    </div>

    {{-- ==================== ONGLET TRAITEMENTS ==================== --}}
    <div class="{{ $activeTab === 'treatments' ? '' : 'hidden' }}">
        <x-card title="Historique des traitements" icon="o-document-check" separator>
            @if($patient->treatments->count() > 0)
                <x-table :headers="[
                    ['key' => 'treatment_date', 'label' => 'Date'],
                    ['key' => 'treatment_time', 'label' => 'Heure'],
                    ['key' => 'care_provided', 'label' => 'Soins'],
                    ['key' => 'patient_condition', 'label' => 'État'],
                    ['key' => 'doctor.name', 'label' => 'Médecin'],
                ]" :rows="$patient->treatments">
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
                </x-table>
            @else
                <div class="text-center py-8 text-base-content/60">Aucun traitement enregistré</div>
            @endif
        </x-card>
    </div>
</div>
