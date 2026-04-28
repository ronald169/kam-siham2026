<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MedecineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'doctor_id' => null,
            'consultation_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => fake()->randomElement(['in_progress', 'completed']),

            'consultation_reason' => fake()->paragraph(),
            'illness_history' => fake()->paragraph(3),
            'medical_history' => fake()->paragraph(),
            'physical_examination' => fake()->paragraph(),
            'diagnostic_hypothesis' => fake()->paragraph(),

            'requested_exams' => fake()->paragraph(),
            'exam_results' => null,

            'prescribed_treatments' => fake()->paragraph(),
            'medical_prescriptions' => fake()->paragraph(),

            'next_appointment' => fake()->optional(0.6)->dateTimeBetween('now', '+1 month'),
            'follow_up_instructions' => fake()->optional(0.5)->paragraph(),

            'documents' => null,
        ];
    }
}
