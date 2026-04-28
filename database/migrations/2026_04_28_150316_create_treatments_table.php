<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();

            // Relation polymorphique pour lier à toxicologie, psychopathologie ou médecine
            $table->morphs('treatable');

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');

            $table->date('treatment_date');
            $table->time('treatment_time')->nullable();

            // === TRAITEMENT ===
            $table->text('observations')->nullable(); // Observations du jour
            $table->text('medications_given')->nullable(); // Médicaments administrés
            $table->text('care_provided')->nullable(); // Soins prodigués
            $table->decimal('cost', 10, 2)->nullable(); // Coût

            // === ÉVALUATION ===
            $table->string('patient_condition')->nullable(); // État du patient (stable, amélioré, dégradé)
            $table->text('doctor_notes')->nullable(); // Notes du médecin
            $table->text('next_instructions')->nullable(); // Instructions pour prochain traitement

            $table->timestamps();

            $table->index(['treatable_id', 'treatable_type']);
            $table->index('treatment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
