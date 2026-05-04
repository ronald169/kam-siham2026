<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // Numéro dossier unique (format: KAM-2025-0001)
            $table->string('medical_record_number')->unique();

            // === INFORMATIONS PERSONNELLES (TOUS NULLABLE) ===
            $table->string('name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable(); // Calculé automatiquement
            $table->string('sex')->nullable(); // Homme, Femme, Autre
            $table->string('place_of_birth')->nullable(); // Lieu de naissance
            $table->string('nationality')->nullable(); // Nationalité
            $table->string('tribe')->nullable(); // Tribu
            $table->string('religion')->nullable();
            $table->string('study_level')->nullable(); // Niveau d'étude
            $table->string('profession')->nullable(); // Profession
            $table->string('marital_status')->nullable(); // Situation matrimoniale

            // === CONTACTS (NOUVEAU) ===
            $table->string('patient_phone')->nullable(); // Téléphone du patient
            $table->string('patient_email')->nullable(); // Email du patient
            $table->string('patient_address')->nullable(); // Adresse du patient
            $table->string('emergency_contact_name')->nullable(); // Nom du contact d'urgence
            $table->string('emergency_contact_phone')->nullable(); // Téléphone contact urgence
            $table->string('emergency_contact_relation')->nullable(); // Lien avec le patient

            // === DYNAMIQUE FAMILIALE (NOUVEAU) ===
            $table->string('family_dynamics')->nullable(); // Dynamique familiale
            $table->string('type_of_family')->nullable(); // Type de famille (monoparentale, élargie, etc.)
            $table->string('order_child')->nullable(); // Rang de l'enfant

            // === ANTÉCÉDENTS ===
            $table->text('childhood_antecedents')->nullable(); // Antécédents enfance
            $table->text('medical_antecedents')->nullable(); // Antécédents médicaux
            $table->text('surgical_antecedents')->nullable(); // Antécédents chirurgicaux
            $table->text('family_conflicts')->nullable(); // Conflits familiaux
            $table->text('health_problems')->nullable(); // Problèmes de santé

            // === RELATIONS FAMILIALES - PÈRE ===
            $table->string('father_name')->nullable();
            $table->integer('father_age')->nullable();
            $table->string('father_religion')->nullable();
            $table->string('father_profession')->nullable();
            $table->string('father_health_status')->nullable();
            $table->string('father_education_level')->nullable();
            $table->boolean('father_alive')->default(true);

            // === RELATIONS FAMILIALES - MÈRE ===
            $table->string('mother_name')->nullable();
            $table->integer('mother_age')->nullable();
            $table->string('mother_religion')->nullable();
            $table->string('mother_profession')->nullable();
            $table->string('mother_health_status')->nullable();
            $table->string('mother_education_level')->nullable();
            $table->boolean('mother_alive')->default(true);

            // === RELATIONS ENTRE PARENTS ET ENFANTS ===
            $table->string('parents_relationship')->nullable(); // Relation entre parents
            $table->string('siblings_relationship')->nullable(); // Relation entre frères/sœurs
            $table->string('privileged_relationship')->nullable(); // Relation privilégiée
            $table->string('frequency_stay_with_parents')->nullable();
            $table->integer('number_of_children_at_home')->nullable();
            $table->json('children_list')->nullable(); // Liste des enfants

            // === RELATIONS SOCIALES ===
            $table->integer('number_of_friends')->nullable();
            $table->integer('number_of_true_friends')->nullable();
            $table->string('intimate_friends_quality')->nullable(); // Qualité des amis intimes
            $table->string('social_relations_nature')->nullable(); // Nature des relations sociales
            $table->string('leisure_activities')->nullable(); // Loisirs

            // === PSYCHOLOGIE ===
            $table->text('self_relationship')->nullable(); // Relation avec soi-même
            $table->text('self_perceived_problems')->nullable(); // Problèmes perçus
            $table->text('self_judgment')->nullable(); // Jugement sur soi
            $table->text('expectations_from_psychologist')->nullable(); // Attentes du psychologue

            // === INFORMATIONS MÉDICALES GÉNÉRALES ===
            $table->text('allergies')->nullable();
            $table->text('current_treatments')->nullable();
            $table->string('blood_type')->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // Poids (kg)
            $table->decimal('height', 5, 2)->nullable(); // Taille (cm)

            // === STATUT DU PATIENT ===
            $table->string('status')->default('active'); // active, discharged, transferred, deceased
            $table->date('admission_date')->nullable();
            $table->date('discharge_date')->nullable();
            $table->text('discharge_reason')->nullable();

            // === MÉDECIN RÉFÉRENT ===
            $table->foreignId('referring_doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');

            $table->softDeletes();
            $table->timestamps();

            // Index pour recherche rapide
            $table->index(['name', 'medical_record_number']);
            $table->index('status');
            $table->index('admission_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
