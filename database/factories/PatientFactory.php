<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        $sex = fake()->randomElement(['Homme', 'Femme']);
        $admissionDate = fake()->dateTimeBetween('-6 months', 'now');
        $birthDate = fake()->dateTimeBetween('-80 years', '-5 years');

        return [
            'medical_record_number' => 'KAM-' . fake()->numberBetween(2022, 2026) . '-' . fake()->numberBetween(1, 9999),
            'name' => fake()->name($sex === 'Homme' ? 'male' : 'female'),
            'date_of_birth' => $birthDate,
            'age' => fake()->numberBetween(5, 80),
            'sex' => $sex,
            'place_of_birth' => fake()->city() . ', Cameroun',
            'nationality' => 'Camerounaise',
            'tribe' => fake()->randomElement(['Bamiléké', 'Béti', 'Douala', 'Foulbé', 'Haoussa', 'Ewondo', 'Bassa']),
            'religion' => fake()->randomElement(['Catholique', 'Protestant', 'Musulman', 'Pentecôtiste', 'Aucune']),
            'study_level' => fake()->randomElement(['Aucun', 'Primaire', 'Collège', 'Lycée', 'Université', 'Master', 'Doctorat']),
            'profession' => fake()->randomElement(['Étudiant', 'Enseignant', 'Commerçant', 'Fonctionnaire', 'Ménagère', 'Chauffeur', 'Infirmier', 'Avocat', 'Sans emploi']),
            'marital_status' => fake()->randomElement(['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve', 'Concubinage']),

            // Contacts
            'patient_phone' => fake()->phoneNumber(),
            'patient_email' => fake()->optional(0.7)->email(),
            'patient_address' => fake()->address(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'emergency_contact_relation' => fake()->randomElement(['Père', 'Mère', 'Frère', 'Sœur', 'Conjoint(e)', 'Tante', 'Oncle']),

            // Dynamique familiale
            'family_dynamics' => fake()->optional(0.8)->paragraph(),
            'type_of_family' => fake()->randomElement(['Monoparentale', 'Biparentale', 'Élargie', 'Recomposée', 'Famille d\'accueil']),
            'order_child' => fake()->optional(0.7)->randomElement(['Aîné', 'Cadet', 'Benjamin', 'Enfant unique', '2ème', '3ème', '4ème']),

            // Antécédents
            'childhood_antecedents' => fake()->optional(0.5)->paragraph(),
            'medical_antecedents' => fake()->optional(0.6)->sentence(),
            'surgical_antecedents' => fake()->optional(0.3)->sentence(),
            'family_conflicts' => fake()->optional(0.5)->paragraph(),
            'health_problems' => fake()->optional(0.4)->sentence(),

            // Père
            'father_name' => fake()->optional(0.9)->name('male'),
            'father_age' => fake()->optional(0.8)->numberBetween(35, 90),
            'father_religion' => fake()->optional(0.7)->randomElement(['Catholique', 'Protestant', 'Musulman']),
            'father_profession' => fake()->optional(0.8)->randomElement(['Agriculteur', 'Commerçant', 'Fonctionnaire', 'Chauffeur', 'Enseignant', 'Ingénieur', 'Médecin']),
            'father_health_status' => fake()->optional(0.6)->randomElement(['Bon', 'Moyen', 'Médiocre', 'Décédé']),
            'father_education_level' => fake()->optional(0.7)->randomElement(['Primaire', 'Secondaire', 'Université', 'Aucun']),
            'father_alive' => fake()->boolean(85), // 85% vivant

            // Mère
            'mother_name' => fake()->optional(0.9)->name('female'),
            'mother_age' => fake()->optional(0.8)->numberBetween(35, 90),
            'mother_religion' => fake()->optional(0.7)->randomElement(['Catholique', 'Protestant', 'Musulman']),
            'mother_profession' => fake()->optional(0.8)->randomElement(['Ménagère', 'Commerçante', 'Enseignante', 'Infirmière', 'Couturière', 'Fonctionnaire']),
            'mother_health_status' => fake()->optional(0.6)->randomElement(['Bon', 'Moyen', 'Médiocre', 'Décédé']),
            'mother_education_level' => fake()->optional(0.7)->randomElement(['Primaire', 'Secondaire', 'Université', 'Aucun']),
            'mother_alive' => fake()->boolean(90), // 90% vivant

            // Relations familiales
            'parents_relationship' => fake()->optional(0.7)->randomElement(['Harmonieuse', 'Conflictuelle', 'Séparés', 'Divorcés', 'Décédés']),
            'siblings_relationship' => fake()->optional(0.6)->randomElement(['Bonne', 'Moyenne', 'Conflictuelle', 'Absence de fratrie']),
            'privileged_relationship' => fake()->optional(0.5)->randomElement(['Mère', 'Père', 'Grand-mère', 'Grand-père', 'Tante', 'Oncle', 'Frère', 'Sœur']),
            'frequency_stay_with_parents' => fake()->optional(0.6)->randomElement(['Permanent', 'Régulier', 'Occasionnel', 'Rare', 'Plus']),
            'number_of_children_at_home' => fake()->optional(0.5)->numberBetween(0, 8),
            'children_list' => fake()->optional(0.3)->passthrough(
                json_encode([
                    ['name' => fake()->firstName(), 'age' => fake()->numberBetween(1, 30)],
                    ['name' => fake()->firstName(), 'age' => fake()->numberBetween(1, 30)],
                ])
            ),

            // Relations sociales
            'number_of_friends' => fake()->optional(0.7)->numberBetween(0, 20),
            'number_of_true_friends' => fake()->optional(0.6)->numberBetween(0, 10),
            'intimate_friends_quality' => fake()->optional(0.5)->randomElement(['Confiance', 'Stable', 'Superficiel', 'Instable']),
            'social_relations_nature' => fake()->optional(0.5)->paragraph(),
            'leisure_activities' => fake()->optional(0.7)->randomElement(['Sport', 'Lecture', 'Musique', 'Cinéma', 'Jeux vidéo', 'Sorties entre amis', 'Télévision']),

            // Psychologie
            'self_relationship' => fake()->optional(0.4)->paragraph(),
            'self_perceived_problems' => fake()->optional(0.5)->paragraph(),
            'self_judgment' => fake()->optional(0.4)->paragraph(),
            'expectations_from_psychologist' => fake()->optional(0.6)->paragraph(),

            // Informations médicales
            'allergies' => fake()->optional(0.3)->sentence(),
            'current_treatments' => fake()->optional(0.4)->sentence(),
            'blood_type' => fake()->optional(0.5)->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'weight' => fake()->optional(0.7)->numberBetween(40, 120),
            'height' => fake()->optional(0.7)->numberBetween(140, 190),

            // Statut
            'status' => fake()->randomElement(['active', 'discharged', 'transferred']),
            'admission_date' => $admissionDate,
            'discharge_date' => fake()->optional(0.3)->dateTimeBetween($admissionDate, 'now'),
            'discharge_reason' => fake()->optional(0.2)->sentence(),

            'referring_doctor_id' => null, // Sera assigné après
            'created_by' => 1,
        ];
    }
}
