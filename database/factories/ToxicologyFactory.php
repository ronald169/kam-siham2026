<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ToxicologyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null, // Sera assigné après
            'doctor_id' => null, // Sera assigné après
            'consultation_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => fake()->randomElement(['in_progress', 'completed']),

            // Histoire des conduites addictives
            'substances_used' => fake()->randomElement(['Cannabis', 'Cocaine', 'Héroïne', 'Alcool', 'Médicaments', 'Tabac', 'Produits inhalés']),
            'substances_start_age' => fake()->numberBetween(12, 30),
            'substances_start_reason' => fake()->sentence(),
            'current_consumption_motivation' => fake()->sentence(),
            'tolerance_description' => fake()->paragraph(),
            'withdrawal_attempts' => fake()->paragraph(),
            'stop_motivation' => fake()->sentence(),
            'substances_types' => fake()->sentence(),
            'max_abstinence_duration' => fake()->randomElement(['1 jour', '1 semaine', '1 mois', '6 mois', '1 an', 'Jamais']),

            // Conséquences
            'substance_relation' => fake()->randomElement(['Forte dépendance', 'Dépendance modérée', 'Usage occasionnel']),
            'weight_loss' => fake()->boolean(60),
            'pale_complexion' => fake()->boolean(40),
            'withdrawal_insomnia' => fake()->boolean(70),
            'nightmares' => fake()->boolean(50),
            'hallucinations' => fake()->boolean(30),
            'somatic_disorders' => fake()->boolean(45),
            'behavioral_delirium' => fake()->boolean(35),
            'legal_issues' => fake()->boolean(25),
            'affective_fulfillment' => fake()->randomElement(['Bon', 'Moyen', 'Mauvais']),
            'sexual_fulfillment' => fake()->randomElement(['Bon', 'Moyen', 'Mauvais']),

            // Évaluation
            'consumption_pattern' => fake()->paragraph(),
            'dependency_investment' => fake()->paragraph(),

            // Tableau clinique
            'general_condition' => fake()->randomElement(['Bon', 'Passable', 'Mauvais', 'Critique']),
            'respiratory_signs' => fake()->optional(0.5)->sentence(),
            'neurological_signs' => fake()->optional(0.5)->sentence(),
            'psychiatric_disorders' => fake()->optional(0.6)->sentence(),
            'other_symptoms' => fake()->optional(0.4)->sentence(),

            // Antécédents
            'medical_surgical_history' => fake()->optional(0.5)->paragraph(),
            'allergy_history' => fake()->optional(0.3)->sentence(),
            'psychiatric_history' => fake()->optional(0.5)->paragraph(),
            'trauma_history' => fake()->optional(0.4)->sentence(),

            // Examens et conclusion
            'psychological_assessment' => fake()->optional(0.7)->paragraph(),
            'biological_assessment' => fake()->optional(0.6)->paragraph(),
            'diagnostic_conclusion' => fake()->paragraph(),
            'treatment_plan' => fake()->paragraph(),
            'recommendations' => fake()->paragraph(),

            'documents' => null, // Pour les fichiers
        ];
    }
}
