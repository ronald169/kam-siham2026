<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychopathologies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');

            $table->date('consultation_date')->nullable();
            $table->string('status')->default('in_progress');

            // === ANAMNÈSE ===
            $table->text('chief_complaint')->nullable(); // Motif principal
            $table->text('illness_history')->nullable(); // Histoire de la maladie

            // === EXAMEN PSYCHIATRIQUE - PRÉSENTATION ===
            $table->text('appearance')->nullable(); // Tenue, aspect physique
            $table->text('facial_expressions')->nullable(); // Mimique
            $table->text('gait')->nullable(); // Démarche
            $table->text('eye_contact')->nullable(); // Regard
            $table->text('other_presentation')->nullable();
            $table->text('contact_quality')->nullable(); // Qualité du contact

            // === COMPORTEMENT ===
            $table->boolean('agitation')->nullable();
            $table->boolean('impulses')->nullable();
            $table->boolean('stupor')->nullable();
            $table->boolean('catalepsy')->nullable();
            $table->boolean('tics')->nullable();
            $table->text('other_behaviors')->nullable();

            // === SOMMEIL ===
            $table->boolean('insomnia')->nullable();
            $table->boolean('daytime_sleepiness')->nullable();
            $table->boolean('hypersomnia')->nullable();
            $table->boolean('dream_disturbances')->nullable();
            $table->text('other_sleep_issues')->nullable();

            // === CONDUITES ALIMENTAIRES ===
            $table->boolean('food_restriction')->nullable();
            $table->boolean('food_refusal')->nullable();
            $table->boolean('excessive_eating')->nullable();
            $table->boolean('excessive_drinking')->nullable();
            $table->text('other_eating_behaviors')->nullable();

            // === VIE SEXUELLE ET AFFECTIVE ===
            $table->string('sexual_orientation')->nullable();
            $table->string('sexual_activity_frequency')->nullable();
            $table->boolean('masturbation')->nullable();
            $table->boolean('impotence')->nullable();
            $table->text('other_sexual_issues')->nullable();

            // === TROUBLES DES CONDUITES SOCIALES ===
            $table->boolean('suicidal_ideation')->nullable();
            $table->boolean('suicide_attempts')->nullable();
            $table->boolean('suicidal_equivalents')->nullable();
            $table->boolean('runaway')->nullable();
            $table->boolean('pathological_stealing')->nullable();
            $table->boolean('sexual_offenses')->nullable();
            $table->text('other_social_conduct_disorders')->nullable();

            // === ADDICTIONS ===
            $table->boolean('alcoholism')->nullable();
            $table->boolean('smoking')->nullable();
            $table->text('other_addictions')->nullable();

            // === TROUBLES DU LANGAGE ===
            $table->text('speech_disorders')->nullable();

            // === TROUBLES DE LA MÉMOIRE ===
            $table->text('memory_disorders')->nullable();

            // === TROUBLES DE LA PENSÉE ===
            $table->text('thought_flow_disorders')->nullable(); // Cours de la pensée
            $table->text('thought_content_disorders')->nullable(); // Contenu de la pensée
            $table->text('global_thought_distortion')->nullable(); // Distorsion globale

            // === JUGEMENT ===
            $table->text('judgment_disorders')->nullable();

            // === PERCEPTION ===
            $table->text('hallucinations')->nullable();

            // === CONSCIENCE ET VIGILANCE ===
            $table->text('attention_quality')->nullable();
            $table->text('spatiotemporal_orientation')->nullable();
            $table->boolean('hypovigilance_hypervigilance')->nullable();
            $table->boolean('twilight_states')->nullable(); // États crépusculaires
            $table->boolean('oniric_states')->nullable(); // États oniroïdes
            $table->text('other_consciousness_disorders')->nullable();

            // === AFFECTS ET HUMEUR ===
            $table->text('affect_expression_disorders')->nullable();
            $table->text('mood_disorders')->nullable();

            // === EXAMEN PHYSIQUE ===
            $table->text('vital_signs')->nullable();
            $table->text('general_condition')->nullable();
            $table->text('cardiovascular_exam')->nullable();
            $table->text('pulmonary_exam')->nullable();
            $table->text('neurological_exam')->nullable();

            // === CONCLUSION ===
            $table->text('clinical_conclusion')->nullable();
            $table->text('diagnostic_discussion')->nullable();
            $table->text('psychological_assesment_summary')->nullable();
            $table->text('treatment_recommendations')->nullable();

            // === STOCKAGE FICHIERS ===
            $table->json('documents')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychopathologies');
    }
};
