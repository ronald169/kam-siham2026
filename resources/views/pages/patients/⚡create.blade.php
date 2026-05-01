<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Patient;
use App\Models\User;

new
#[Title('Nouveau patient')]
class extends Component {
    use Toast;

    // === IDENTITÉ ===
    public string $name = '';
    public string $medical_record_number = '';
    public string $sex = '';
    public ?string $date_of_birth = null;
    public ?string $place_of_birth = '';
    public ?string $nationality = 'Camerounaise';
    public ?string $tribe = '';
    public ?string $religion = '';
    public ?string $profession = '';
    public ?string $study_level = '';
    public ?string $marital_status = '';

    // === CONTACTS ===
    public ?string $patient_phone = '';
    public ?string $patient_email = '';
    public ?string $patient_address = '';
    public ?string $emergency_contact_name = '';
    public ?string $emergency_contact_phone = '';
    public ?string $emergency_contact_relation = '';

    // === DYNAMIQUE FAMILIALE ===
    public ?string $family_dynamics = '';
    public ?string $type_of_family = '';
    public ?string $order_child = '';

    // === PARENTS ===
    public ?string $father_name = '';
    public ?int $father_age = null;
    public ?string $father_profession = '';
    public ?string $father_health_status = '';
    public ?string $father_education_level = '';
    public bool $father_alive = true;

    public ?string $mother_name = '';
    public ?int $mother_age = null;
    public ?string $mother_profession = '';
    public ?string $mother_health_status = '';
    public ?string $mother_education_level = '';
    public bool $mother_alive = true;

    // === RELATIONS FAMILIALES ===
    public ?string $parents_relationship = '';
    public ?string $siblings_relationship = '';
    public ?string $privileged_relationship = '';
    public ?string $frequency_stay_with_parents = '';
    public ?int $number_of_children_at_home = null;

    // === SOCIAL ===
    public ?int $number_of_friends = null;
    public ?int $number_of_true_friends = null;
    public ?string $intimate_friends_quality = '';
    public ?string $social_relations_nature = '';
    public ?string $leisure_activities = '';

    // === PSYCHOLOGIE ===
    public ?string $self_relationship = '';
    public ?string $self_perceived_problems = '';
    public ?string $self_judgment = '';
    public ?string $expectations_from_psychologist = '';

    // === ANTÉCÉDENTS ===
    public ?string $childhood_antecedents = '';
    public ?string $medical_antecedents = '';
    public ?string $surgical_antecedents = '';
    public ?string $family_conflicts = '';
    public ?string $health_problems = '';

    // === MÉDICAL ===
    public ?string $blood_type = '';
    public ?string $allergies = '';
    public ?string $current_treatments = '';
    public ?float $weight = null;
    public ?float $height = null;
    public ?int $referring_doctor_id = null;

    public string $activeSection = 'identity';

    public function mount()
    {
        $lastPatient = Patient::latest('id')->first();
        $lastNumber = $lastPatient ? intval(substr($lastPatient->medical_record_number, -4)) : 0;
        $this->medical_record_number = 'KAM-' . date('Y') . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        if (auth()->user()->isMedecin()) {
            $this->referring_doctor_id = auth()->id();
        }
    }

    public function getDoctorsProperty()
    {
        return User::where('role', 'medecin')->get();
    }

    public function getTribesProperty()
    {
        return [
            ['id' => 'Bamiléké', 'name' => 'Bamiléké'],
            ['id' => 'Béti', 'name' => 'Béti'],
            ['id' => 'Douala', 'name' => 'Douala'],
            ['id' => 'Foulbé', 'name' => 'Foulbé'],
            ['id' => 'Haoussa', 'name' => 'Haoussa'],
            ['id' => 'Ewondo', 'name' => 'Ewondo'],
            ['id' => 'Bassa', 'name' => 'Bassa'],
            ['id' => 'Autre', 'name' => 'Autre'],
        ];
    }

    public function getReligionsProperty()
    {
        return [
            ['id' => 'Catholique', 'name' => 'Catholique'],
            ['id' => 'Protestant', 'name' => 'Protestant'],
            ['id' => 'Musulman', 'name' => 'Musulman'],
            ['id' => 'Pentecôtiste', 'name' => 'Pentecôtiste'],
            ['id' => 'Aucune', 'name' => 'Aucune'],
            ['id' => 'Autre', 'name' => 'Autre'],
        ];
    }

    public function getStudyLevelsProperty()
    {
        return [
            ['id' => 'Aucun', 'name' => 'Aucun'],
            ['id' => 'Primaire', 'name' => 'Primaire'],
            ['id' => 'Collège', 'name' => 'Collège'],
            ['id' => 'Lycée', 'name' => 'Lycée'],
            ['id' => 'Université', 'name' => 'Université'],
            ['id' => 'Master', 'name' => 'Master'],
            ['id' => 'Doctorat', 'name' => 'Doctorat'],
        ];
    }

    public function getMaritalStatusesProperty()
    {
        return [
            ['id' => 'Célibataire', 'name' => 'Célibataire'],
            ['id' => 'Marié(e)', 'name' => 'Marié(e)'],
            ['id' => 'Divorcé(e)', 'name' => 'Divorcé(e)'],
            ['id' => 'Veuf/Veuve', 'name' => 'Veuf/Veuve'],
            ['id' => 'Concubinage', 'name' => 'Concubinage'],
        ];
    }

    public function getBloodTypesProperty()
    {
        return [
            ['id' => 'A+', 'name' => 'A+'],
            ['id' => 'A-', 'name' => 'A-'],
            ['id' => 'B+', 'name' => 'B+'],
            ['id' => 'B-', 'name' => 'B-'],
            ['id' => 'O+', 'name' => 'O+'],
            ['id' => 'O-', 'name' => 'O-'],
            ['id' => 'AB+', 'name' => 'AB+'],
            ['id' => 'AB-', 'name' => 'AB-'],
        ];
    }

    public function getFamilyTypesProperty()
        {
            return [
                ['id' => 'Monoparentale', 'name' => 'Monoparentale'],
                ['id' => 'Biparentale', 'name' => 'Biparentale'],
                ['id' => 'Élargie', 'name' => 'Élargie'],
                ['id' => 'Recomposée', 'name' => 'Recomposée'],
                ['id' => 'Famille d\'accueil', 'name' => 'Famille d\'accueil'],
            ];
        }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'medical_record_number' => 'required|string|unique:patients',
            'sex' => 'required|in:Homme,Femme',
            'date_of_birth' => 'nullable|date',
            'patient_phone' => 'nullable|string|max:20',
            'referring_doctor_id' => 'nullable|exists:users,id',
        ]);

        $patient = Patient::create([
            // Identité
            'name' => $this->name,
            'medical_record_number' => $this->medical_record_number,
            'sex' => $this->sex,
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->date_of_birth ? \Carbon\Carbon::parse($this->date_of_birth)->age : null,
            'place_of_birth' => $this->place_of_birth,
            'nationality' => $this->nationality,
            'tribe' => $this->tribe,
            'religion' => $this->religion,
            'profession' => $this->profession,
            'study_level' => $this->study_level,
            'marital_status' => $this->marital_status,
            'patient_address' => $this->patient_address,

            // Contacts
            'patient_phone' => $this->patient_phone,
            'patient_email' => $this->patient_email,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relation' => $this->emergency_contact_relation,

            // Dynamique familiale
            'family_dynamics' => $this->family_dynamics,
            'type_of_family' => $this->type_of_family,
            'order_child' => $this->order_child,

            // Parents
            'father_name' => $this->father_name,
            'father_age' => $this->father_age,
            'father_profession' => $this->father_profession,
            'father_health_status' => $this->father_health_status,
            'father_education_level' => $this->father_education_level,
            'father_alive' => $this->father_alive,
            'mother_name' => $this->mother_name,
            'mother_age' => $this->mother_age,
            'mother_profession' => $this->mother_profession,
            'mother_health_status' => $this->mother_health_status,
            'mother_education_level' => $this->mother_education_level,
            'mother_alive' => $this->mother_alive,

            // Relations familiales
            'parents_relationship' => $this->parents_relationship,
            'siblings_relationship' => $this->siblings_relationship,
            'privileged_relationship' => $this->privileged_relationship,
            'frequency_stay_with_parents' => $this->frequency_stay_with_parents,
            'number_of_children_at_home' => $this->number_of_children_at_home,

            // Social
            'number_of_friends' => $this->number_of_friends,
            'number_of_true_friends' => $this->number_of_true_friends,
            'intimate_friends_quality' => $this->intimate_friends_quality,
            'social_relations_nature' => $this->social_relations_nature,
            'leisure_activities' => $this->leisure_activities,

            // Psychologie
            'self_relationship' => $this->self_relationship,
            'self_perceived_problems' => $this->self_perceived_problems,
            'self_judgment' => $this->self_judgment,
            'expectations_from_psychologist' => $this->expectations_from_psychologist,

            // Antécédents
            'childhood_antecedents' => $this->childhood_antecedents,
            'medical_antecedents' => $this->medical_antecedents,
            'surgical_antecedents' => $this->surgical_antecedents,
            'family_conflicts' => $this->family_conflicts,
            'health_problems' => $this->health_problems,

            // Médical
            'blood_type' => $this->blood_type,
            'allergies' => $this->allergies,
            'current_treatments' => $this->current_treatments,
            'weight' => $this->weight,
            'height' => $this->height,
            'referring_doctor_id' => $this->referring_doctor_id,

            // Admin
            'created_by' => auth()->id(),
            'status' => 'active',
            'admission_date' => now(),
        ]);

        $this->success('Patient créé avec succès.', redirectTo: route('patients.show', $patient->id));
    }

    public function render()
    {
        return $this->view([
            'doctors' => $this->doctors,
            'tribes' => $this->tribes,
            'religions' => $this->religions,
            'studyLevels' => $this->studyLevels,
            'maritalStatuses' => $this->maritalStatuses,
            'bloodTypes' => $this->bloodTypes,
            'familyTypes' => $this->familyTypes,
        ]);
    }
};

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Nouveau patient</h1>
        <p class="text-base-content/70 mt-1">Créer un nouveau dossier patient complet</p>
    </div>

    <x-card>
        {{-- Navigation sections --}}
        <div class="tabs tabs-boxed mb-6 overflow-x-auto flex-wrap">
            <a class="tab {{ $activeSection === 'identity' ? 'tab-active' : '' }}" wire:click="$set('activeSection', 'identity')">Identité</a>
            <a class="tab {{ $activeSection === 'contact' ? 'tab-active' : '' }}" wire:click="$set('activeSection', 'contact')">Contacts</a>
            <a class="tab {{ $activeSection === 'family' ? 'tab-active' : '' }}" wire:click="$set('activeSection', 'family')">Famille</a>
            <a class="tab {{ $activeSection === 'parents' ? 'tab-active' : '' }}" wire:click="$set('activeSection', 'parents')">Parents</a>
            <a class="tab {{ $activeSection === 'social' ? 'tab-active' : '' }}" wire:click="$set('activeSection', 'social')">Social</a>
            <a class="tab {{ $activeSection === 'medical' ? 'tab-active' : '' }}" wire:click="$set('activeSection', 'medical')">Médical</a>
        </div>

        <x-form wire:submit="save">

            {{-- Section 1: Identité --}}
            <div class="{{ $activeSection === 'identity' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nom complet" wire:model="name" required icon="o-user" />
                    <x-input label="N° Dossier" wire:model="medical_record_number" readonly icon="o-identification" />
                    <x-select label="Sexe" wire:model="sex" required :options="[['id' => 'Homme', 'name' => 'Homme'], ['id' => 'Femme', 'name' => 'Femme']]" icon="o-user" />
                    <x-datepicker label="Date de naissance" wire:model="date_of_birth" icon="o-cake" />
                    <x-input label="Lieu de naissance" wire:model="place_of_birth" icon="o-map-pin" />
                    <x-input label="Nationalité" wire:model="nationality" icon="o-flag" />
                    <x-select label="Tribu" wire:model="tribe" :options="$tribes" placeholder="Sélectionner" icon="o-user-group" />
                    <x-select label="Religion" wire:model="religion" :options="$religions" placeholder="Sélectionner" icon="o-building-library" />
                    <x-input label="Profession" wire:model="profession" icon="o-briefcase" />
                    <x-select label="Niveau d'étude" wire:model="study_level" :options="$studyLevels" placeholder="Sélectionner" icon="o-academic-cap" />
                    <x-select label="Situation matrimoniale" wire:model="marital_status" :options="$maritalStatuses" placeholder="Sélectionner" icon="o-heart" />
                    <div class="md:col-span-2">
                        <x-textarea label="Adresse" wire:model="patient_address" icon="o-map-pin" rows="2" />
                    </div>
                </div>
            </div>

            {{-- Section 2: Contacts --}}
            <div class="{{ $activeSection === 'contact' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Téléphone" wire:model="patient_phone" icon="o-phone" />
                    <x-input label="Email" wire:model="patient_email" icon="o-envelope" type="email" />

                    <div class="col-span-2 divider">Contact d'urgence</div>

                    <x-input label="Nom" wire:model="emergency_contact_name" icon="o-user" />
                    <x-input label="Téléphone" wire:model="emergency_contact_phone" icon="o-phone" />
                    <x-input label="Lien de parenté" wire:model="emergency_contact_relation" icon="o-link" placeholder="Père, Mère, Frère, etc." />
                </div>
            </div>

            {{-- Section 3: Famille --}}
            <div class="{{ $activeSection === 'family' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Type de famille" wire:model="type_of_family" :options="$familyTypes" placeholder="Sélectionner" icon="o-home" />
                    <x-input label="Rang de l'enfant" wire:model="order_child" icon="o-numbered-list" placeholder="Aîné, Cadet, Benjamin..." />
                    <x-textarea label="Dynamique familiale" wire:model="family_dynamics" icon="o-home" rows="3" placeholder="Décrivez la dynamique familiale..." />

                    <div class="col-span-2 divider">Relations familiales</div>

                    <x-input label="Relation entre parents" wire:model="parents_relationship" icon="o-heart" />
                    <x-input label="Relation entre frères/sœurs" wire:model="siblings_relationship" icon="o-user-group" />
                    <x-input label="Relation privilégiée" wire:model="privileged_relationship" icon="o-star" />
                    <x-input label="Fréquence de retour chez les parents" wire:model="frequency_stay_with_parents" />
                    <x-input label="Nombre d'enfants à domicile" wire:model="number_of_children_at_home" type="number" icon="o-user" />
                </div>
            </div>

            {{-- Section 4: Parents --}}
            <div class="{{ $activeSection === 'parents' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-card title="Père" icon="o-user" separator>
                        <div class="grid grid-cols-1 gap-3">
                            <x-input label="Nom" wire:model="father_name" />
                            <x-input label="Âge" wire:model="father_age" type="number" />
                            <x-input label="Profession" wire:model="father_profession" />
                            <x-input label="État de santé" wire:model="father_health_status" />
                            <x-select label="Niveau d'étude" wire:model="father_education_level" :options="$studyLevels" placeholder="Sélectionner" />
                            <x-toggle label="En vie" wire:model="father_alive" class="mt-2" />
                        </div>
                    </x-card>

                    <x-card title="Mère" icon="o-user" separator>
                        <div class="grid grid-cols-1 gap-3">
                            <x-input label="Nom" wire:model="mother_name" />
                            <x-input label="Âge" wire:model="mother_age" type="number" />
                            <x-input label="Profession" wire:model="mother_profession" />
                            <x-input label="État de santé" wire:model="mother_health_status" />
                            <x-select label="Niveau d'étude" wire:model="mother_education_level" :options="$studyLevels" placeholder="Sélectionner" />
                            <x-toggle label="En vie" wire:model="mother_alive" class="mt-2" />
                        </div>
                    </x-card>
                </div>
            </div>

            {{-- Section 5: Social et Psychologie --}}
            <div class="{{ $activeSection === 'social' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-card title="Relations sociales" icon="o-user-group" separator>
                        <div class="grid grid-cols-1 gap-3">
                            <x-input label="Nombre d'amis" wire:model="number_of_friends" type="number" />
                            <x-input label="Vrais amis" wire:model="number_of_true_friends" type="number" />
                            <x-input label="Qualité des relations intimes" wire:model="intimate_friends_quality" />
                            <x-input label="Nature des relations" wire:model="social_relations_nature" />
                            <x-input label="Loisirs" wire:model="leisure_activities" />
                        </div>
                    </x-card>

                    <x-card title="Psychologie" icon="o-brain-circuit" separator>
                        <div class="grid grid-cols-1 gap-3">
                            <x-textarea label="Relation avec soi-même" wire:model="self_relationship" rows="2" />
                            <x-textarea label="Problèmes perçus" wire:model="self_perceived_problems" rows="2" />
                            <x-textarea label="Jugement sur soi" wire:model="self_judgment" rows="2" />
                            <x-textarea label="Attentes du psychologue" wire:model="expectations_from_psychologist" rows="2" />
                        </div>
                    </x-card>

                    <x-card title="Antécédents" icon="o-document-text" separator class="lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <x-textarea label="Antécédents enfance" wire:model="childhood_antecedents" rows="2" />
                            <x-textarea label="Antécédents médicaux" wire:model="medical_antecedents" rows="2" />
                            <x-textarea label="Antécédents chirurgicaux" wire:model="surgical_antecedents" rows="2" />
                            <x-textarea label="Conflits familiaux" wire:model="family_conflicts" rows="2" />
                            <x-textarea label="Problèmes de santé" wire:model="health_problems" rows="2" class="md:col-span-2" />
                        </div>
                    </x-card>
                </div>
            </div>

            {{-- Section 6: Médical --}}
            <div class="{{ $activeSection === 'medical' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-card title="Paramètres médicaux" icon="o-heart" separator>
                        <div class="grid grid-cols-1 gap-3">
                            <x-select label="Groupe sanguin" wire:model="blood_type" :options="$bloodTypes" placeholder="Non renseigné" />
                            <x-input label="Poids (kg)" wire:model="weight" type="number" step="0.1" />
                            <x-input label="Taille (cm)" wire:model="height" type="number" step="0.1" />
                            <x-select label="Médecin référent" wire:model="referring_doctor_id" :options="$doctors" option-value="id" option-label="name" placeholder="Sélectionner un médecin" />
                        </div>
                    </x-card>

                    <x-card title="Traitements et allergies" icon="o-document-check" separator>
                        <div class="grid grid-cols-1 gap-3">
                            <x-textarea label="Traitements en cours" wire:model="current_treatments" rows="3" placeholder="Liste des traitements actuels..." />
                            <x-textarea label="Allergies" wire:model="allergies" rows="3" placeholder="Liste des allergies connues..." />
                        </div>
                    </x-card>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Annuler" link="{{ route('patients.index') }}" />
                <x-button label="Créer le patient" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
