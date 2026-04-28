<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medecines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');

            $table->date('consultation_date')->nullable();
            $table->string('status')->default('in_progress');

            // === CONSULTATION ===
            $table->text('consultation_reason')->nullable(); // Motif de consultation
            $table->text('illness_history')->nullable(); // Histoire de la maladie
            $table->text('medical_history')->nullable(); // Antécédents
            $table->text('physical_examination')->nullable(); // Examen physique
            $table->text('diagnostic_hypothesis')->nullable(); // Hypothèse diagnostique

            // === EXAMENS ===
            $table->text('requested_exams')->nullable(); // Examens demandés
            $table->json('exam_results')->nullable(); // Résultats d'examens avec fichiers

            // === TRAITEMENT ===
            $table->text('prescribed_treatments')->nullable(); // Traitements prescrits
            $table->text('medical_prescriptions')->nullable(); // Ordonnances

            // === SUIVI ===
            $table->date('next_appointment')->nullable();
            $table->text('follow_up_instructions')->nullable();

            // === STOCKAGE FICHIERS ===
            $table->json('documents')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medecines');
    }
};
