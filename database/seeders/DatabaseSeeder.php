<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Toxicology;
use App\Models\Psychopathology;
use App\Models\Medecine;
use App\Models\Treatment;
use App\Models\Appointment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création des utilisateurs
        $this->command->info('Création des utilisateurs...');

        // Admin principal
        $admin = User::create([
            'name' => 'Dr. Kam Siham',
            'email' => 'admin@kam-siham.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+237 699 123 456',
            'specialty' => 'Directeur médical',
            'is_active' => true,
        ]);

        // Médecins
        $doctors = [
            [
                'name' => 'Dr. Jean Mbarga',
                'email' => 'jean.mbarga@kam-siham.com',
                'specialty' => 'Psychiatre',
                'role' => 'medecin'
            ],
            [
                'name' => 'Dr. Marie Ngo Ngo',
                'email' => 'marie.ngongo@kam-siham.com',
                'specialty' => 'Psychologue clinicienne',
                'role' => 'medecin'
            ],
            [
                'name' => 'Dr. Paul Essomba',
                'email' => 'paul.essomba@kam-siham.com',
                'specialty' => 'Médecin Généraliste',
                'role' => 'medecin'
            ],
            [
                'name' => 'Dr. Claire Ndi',
                'email' => 'claire.ndi@kam-siham.com',
                'specialty' => 'Addictologue',
                'role' => 'medecin'
            ],
            [
                'name' => 'Dr. Samuel Fouda',
                'email' => 'samuel.fouda@kam-siham.com',
                'specialty' => 'Neuropsychiatre',
                'role' => 'medecin'
            ],
        ];

        foreach ($doctors as $doctor) {
            User::create(array_merge($doctor, [
                'password' => Hash::make('password'),
                'phone' => fake()->phoneNumber(),
                'is_active' => true,
            ]));
        }

        // Consultants (lecture seule)
        $consultants = [
            [
                'name' => 'Dr. Consultant 1',
                'email' => 'consultant1@kam-siham.com',
                'specialty' => 'Psychiatre consultant',
            ],
            [
                'name' => 'Dr. Consultant 2',
                'email' => 'consultant2@kam-siham.com',
                'specialty' => 'Psychologue consultant',
            ],
        ];

        foreach ($consultants as $consultant) {
            User::create(array_merge($consultant, [
                'role' => 'consultant',
                'password' => Hash::make('password'),
                'phone' => fake()->phoneNumber(),
                'is_active' => true,
            ]));
        }

        $allDoctors = User::where('role', 'medecin')->get();
        $allUsers = User::all();

        $this->command->info('✓ ' . User::count() . ' utilisateurs créés');

        // 2. Création des patients
        $this->command->info('Création des patients...');

        $patients = Patient::factory(30)->create()->each(function ($patient) use ($allDoctors, $admin) {
            $patient->referring_doctor_id = $allDoctors->random()->id;
            $patient->created_by = $admin->id;
            $patient->save();
        });

        $this->command->info('✓ ' . Patient::count() . ' patients créés');

        // 3. Consultations Toxicologie
        $this->command->info('Création des consultations en toxicologie...');

        $patients->random(15)->each(function ($patient) use ($allDoctors) {
            Toxicology::factory()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $allDoctors->random()->id,
            ]);
        });

        $this->command->info('✓ ' . Toxicology::count() . ' consultations toxicologie créées');

        // 4. Consultations Psychopathologie
        $this->command->info('Création des consultations en psychopathologie...');

        $patients->random(20)->each(function ($patient) use ($allDoctors) {
            Psychopathology::factory()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $allDoctors->random()->id,
            ]);
        });

        $this->command->info('✓ ' . Psychopathology::count() . ' consultations psychopathologie créées');

        // 5. Consultations Médecine
        $this->command->info('Création des consultations en médecine...');

        $patients->random(25)->each(function ($patient) use ($allDoctors) {
            Medecine::factory()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $allDoctors->random()->id,
            ]);
        });

        $this->command->info('✓ ' . Medecine::count() . ' consultations médecine créées');

        // 6. Traitements quotidiens
        $this->command->info('Création des traitements quotidiens...');

        $allToxicologies = Toxicology::all();
        $allPsychopathologies = Psychopathology::all();
        $allMedecines = Medecine::all();

        // Traitements pour toxicologie
        foreach ($allToxicologies as $toxicology) {
            $treatmentCount = rand(1, 5);
            for ($i = 0; $i < $treatmentCount; $i++) {
                Treatment::create([
                    'treatable_type' => 'App\Models\Toxicology',
                    'treatable_id' => $toxicology->id,
                    'patient_id' => $toxicology->patient_id,
                    'doctor_id' => $toxicology->doctor_id,
                    'treatment_date' => fake()->dateTimeBetween($toxicology->consultation_date, 'now'),
                    'treatment_time' => fake()->time(),
                    'observations' => fake()->paragraph(),
                    'medications_given' => fake()->sentence(),
                    'care_provided' => fake()->sentence(),
                    'cost' => fake()->optional(0.6)->numberBetween(5000, 50000),
                    'patient_condition' => fake()->randomElement(['stable', 'amélioré', 'dégradé']),
                    'doctor_notes' => fake()->paragraph(),
                ]);
            }
        }

        // Traitements pour psychopathologie
        foreach ($allPsychopathologies as $psychopathology) {
            $treatmentCount = rand(1, 8);
            for ($i = 0; $i < $treatmentCount; $i++) {
                Treatment::create([
                    'treatable_type' => 'App\Models\Psychopathology',
                    'treatable_id' => $psychopathology->id,
                    'patient_id' => $psychopathology->patient_id,
                    'doctor_id' => $psychopathology->doctor_id,
                    'treatment_date' => fake()->dateTimeBetween($psychopathology->consultation_date, 'now'),
                    'treatment_time' => fake()->time(),
                    'observations' => fake()->paragraph(),
                    'care_provided' => fake()->sentence(),
                    'cost' => fake()->optional(0.6)->numberBetween(10000, 100000),
                    'patient_condition' => fake()->randomElement(['stable', 'amélioré', 'dégradé']),
                    'doctor_notes' => fake()->paragraph(),
                ]);
            }
        }

        // Traitements pour médecine
        foreach ($allMedecines as $medecine) {
            $treatmentCount = rand(1, 3);
            for ($i = 0; $i < $treatmentCount; $i++) {
                Treatment::create([
                    'treatable_type' => 'App\Models\Medecine',
                    'treatable_id' => $medecine->id,
                    'patient_id' => $medecine->patient_id,
                    'doctor_id' => $medecine->doctor_id,
                    'treatment_date' => fake()->dateTimeBetween($medecine->consultation_date, 'now'),
                    'treatment_time' => fake()->time(),
                    'observations' => fake()->paragraph(),
                    'medications_given' => fake()->sentence(),
                    'care_provided' => fake()->sentence(),
                    'cost' => fake()->optional(0.6)->numberBetween(5000, 30000),
                    'patient_condition' => fake()->randomElement(['stable', 'amélioré', 'dégradé']),
                    'doctor_notes' => fake()->paragraph(),
                ]);
            }
        }

        $this->command->info('✓ ' . Treatment::count() . ' traitements créés');

        // 7. Rendez-vous
        $this->command->info('Création des rendez-vous...');

        for ($i = 0; $i < 50; $i++) {
            $patient = $patients->random();
            $doctor = $allDoctors->random();

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_datetime' => fake()->dateTimeBetween('-1 month', '+2 months'),
                'service_type' => fake()->randomElement(['toxicologie', 'psychopathologie', 'medecine']),
                'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
                'reason' => fake()->optional(0.7)->sentence(),
                'notes' => fake()->optional(0.4)->paragraph(),
            ]);
        }

        $this->command->info('✓ ' . Appointment::count() . ' rendez-vous créés');

        // 8. Statistiques finales
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('🎉 SEEDING TERMINÉ AVEC SUCCÈS !');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->table(
            ['Type', 'Nombre créé'],
            [
                ['👥 Utilisateurs', User::count()],
                ['👤 Patients', Patient::count()],
                ['💊 Consultations Toxicologie', Toxicology::count()],
                ['🧠 Consultations Psychopathologie', Psychopathology::count()],
                ['🩺 Consultations Médecine', Medecine::count()],
                ['📋 Traitements', Treatment::count()],
                ['📅 Rendez-vous', Appointment::count()],
            ]
        );
        $this->command->newLine();
        $this->command->info('📝 Comptes de connexion par défaut :');
        $this->command->info('   Admin    : admin@kam-siham.com / password');
        $this->command->info('   Médecin  : jean.mbarga@kam-siham.com / password');
        $this->command->info('   Consultant : consultant1@kam-siham.com / password');
        $this->command->newLine();
    }
}
