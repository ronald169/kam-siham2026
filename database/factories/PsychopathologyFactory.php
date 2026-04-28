<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PsychopathologyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'doctor_id' => null,
            'consultation_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => fake()->randomElement(['in_progress', 'completed']),

            'chief_complaint' => fake()->paragraph(),
            'illness_history' => fake()->paragraph(3),

            // Présentation
            'appearance' => fake()->sentence(),
            'facial_expressions' => fake()->sentence(),
            'gait' => fake()->sentence(),
            'eye_contact' => fake()->sentence(),
            'other_presentation' => fake()->optional(0.3)->sentence(),
            'contact_quality' => fake()->randomElement(['Bon', 'Moyen', 'Difficile', 'Impossible']),

            // Comportement
            'agitation' => fake()->boolean(30),
            'impulses' => fake()->boolean(40),
            'stupor' => fake()->boolean(15),
            'catalepsy' => fake()->boolean(10),
            'tics' => fake()->boolean(20),
            'other_behaviors' => fake()->optional(0.3)->sentence(),

            // Sommeil
            'insomnia' => fake()->boolean(60),
            'daytime_sleepiness' => fake()->boolean(40),
            'hypersomnia' => fake()->boolean(20),
            'dream_disturbances' => fake()->boolean(45),
            'other_sleep_issues' => fake()->optional(0.3)->sentence(),

            // Conduites alimentaires
            'food_restriction' => fake()->boolean(35),
            'food_refusal' => fake()->boolean(25),
            'excessive_eating' => fake()->boolean(30),
            'excessive_drinking' => fake()->boolean(40),
            'other_eating_behaviors' => fake()->optional(0.3)->sentence(),

            // Vie sexuelle
            'sexual_orientation' => fake()->randomElement(['Hétérosexuel', 'Homosexuel', 'Bisexuel', 'Indéfini']),
            'sexual_activity_frequency' => fake()->randomElement(['Régulière', 'Occasionnelle', 'Rare', 'Absente']),
            'masturbation' => fake()->boolean(60),
            'impotence' => fake()->boolean(20),
            'other_sexual_issues' => fake()->optional(0.3)->sentence(),

            // Troubles sociaux
            'suicidal_ideation' => fake()->boolean(50),
            'suicide_attempts' => fake()->boolean(30),
            'suicidal_equivalents' => fake()->boolean(25),
            'runaway' => fake()->boolean(20),
            'pathological_stealing' => fake()->boolean(15),
            'sexual_offenses' => fake()->boolean(10),
            'other_social_conduct_disorders' => fake()->optional(0.3)->sentence(),

            // Addictions
            'alcoholism' => fake()->boolean(40),
            'smoking' => fake()->boolean(55),
            'other_addictions' => fake()->optional(0.4)->sentence(),

            'speech_disorders' => fake()->optional(0.4)->sentence(),
            'memory_disorders' => fake()->optional(0.4)->sentence(),
            'thought_flow_disorders' => fake()->optional(0.4)->sentence(),
            'thought_content_disorders' => fake()->optional(0.4)->sentence(),
            'global_thought_distortion' => fake()->optional(0.3)->sentence(),
            'judgment_disorders' => fake()->optional(0.4)->sentence(),
            'hallucinations' => fake()->optional(0.4)->sentence(),

            // Conscience
            'attention_quality' => fake()->randomElement(['Normale', 'Diminuée', 'Augmentée', 'Fluctuante']),
            'spatiotemporal_orientation' => fake()->randomElement(['Orienté', 'Désorienté temporo-spatial', 'Désorienté partiel']),
            'hypovigilance_hypervigilance' => fake()->boolean(30),
            'twilight_states' => fake()->boolean(15),
            'oniric_states' => fake()->boolean(20),
            'other_consciousness_disorders' => fake()->optional(0.3)->sentence(),

            'affect_expression_disorders' => fake()->optional(0.4)->sentence(),
            'mood_disorders' => fake()->optional(0.5)->sentence(),

            // Examen physique
            'vital_signs' => fake()->optional(0.7)->sentence(),
            'general_condition' => fake()->randomElement(['Bon', 'Passable', 'Mauvais']),
            'cardiovascular_exam' => fake()->optional(0.5)->sentence(),
            'pulmonary_exam' => fake()->optional(0.5)->sentence(),
            'neurological_exam' => fake()->optional(0.5)->sentence(),

            'clinical_conclusion' => fake()->paragraph(),
            'diagnostic_discussion' => fake()->paragraph(),
            'psychological_assesment_summary' => fake()->paragraph(),
            'treatment_recommendations' => fake()->paragraph(),

            'documents' => null,
        ];
    }
}
