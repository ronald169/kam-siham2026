<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toxicologies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');

            // Date de consultation
            $table->date('consultation_date')->nullable();
            $table->string('status')->default('in_progress'); // in_progress, completed

            // === HISTOIRE DES CONDUITES ADDICTIVES ===
            $table->text('substances_used')->nullable(); // Substances consommées (JSON ou texte)
            $table->string('substances_start_age')->nullable(); // Âge de début
            $table->text('substances_start_reason')->nullable(); // Raison du début
            $table->text('current_consumption_motivation')->nullable(); // Motivation actuelle
            $table->text('tolerance_description')->nullable(); // Description de la tolérance
            $table->text('withdrawal_attempts')->nullable(); // Tentatives d'arrêt
            $table->text('stop_motivation')->nullable(); // Motivation à arrêter
            $table->text('substances_types')->nullable(); // Types de substances
            $table->string('max_abstinence_duration')->nullable(); // Durée max d'abstinence

            // === CONSÉQUENCES ===
            $table->string('substance_relation')->nullable(); // Relation avec la substance
            $table->boolean('weight_loss')->nullable(); // Amaigrissement
            $table->boolean('pale_complexion')->nullable(); // Teint sombre
            $table->boolean('withdrawal_insomnia')->nullable(); // Insomnie de manque
            $table->boolean('nightmares')->nullable(); // Cauchemars
            $table->boolean('hallucinations')->nullable();
            $table->boolean('somatic_disorders')->nullable(); // Troubles somatiques
            $table->boolean('behavioral_delirium')->nullable(); // Délire trouble comportement
            $table->boolean('legal_issues')->nullable(); // Problèmes avec la loi
            $table->string('affective_fulfillment')->nullable(); // Épanouissement affectif
            $table->string('sexual_fulfillment')->nullable(); // Épanouissement sexuel

            // === ÉVALUATION ===
            $table->text('consumption_pattern')->nullable(); // Fréquence et consommation
            $table->text('dependency_investment')->nullable(); // Investissement dans la dépendance

            // === TABLEAU CLINIQUE ===
            $table->text('general_condition')->nullable(); // État général
            $table->text('respiratory_signs')->nullable();
            $table->text('neurological_signs')->nullable();
            $table->text('psychiatric_disorders')->nullable();
            $table->text('other_symptoms')->nullable();

            // === ANTÉCÉDENTS ===
            $table->text('medical_surgical_history')->nullable();
            $table->text('allergy_history')->nullable();
            $table->text('psychiatric_history')->nullable();
            $table->text('trauma_history')->nullable();

            // === EXAMENS ===
            $table->text('psychological_assessment')->nullable();
            $table->text('biological_assessment')->nullable();

            // === CONCLUSION ET TRAITEMENT ===
            $table->text('diagnostic_conclusion')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('recommendations')->nullable();

            // === STOCKAGE FICHIERS ===
            $table->json('documents')->nullable(); // Stocke les chemins des fichiers

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toxicologies');
    }
};
