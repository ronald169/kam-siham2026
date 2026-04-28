<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Toxicology extends Model
{
    use HasFactory;

    protected $table = 'toxicologies';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_date',
        'status',
        'substances_used',
        'substances_start_age',
        'substances_start_reason',
        'current_consumption_motivation',
        'tolerance_description',
        'withdrawal_attempts',
        'stop_motivation',
        'substances_types',
        'max_abstinence_duration',
        'substance_relation',
        'weight_loss',
        'pale_complexion',
        'withdrawal_insomnia',
        'nightmares',
        'hallucinations',
        'somatic_disorders',
        'behavioral_delirium',
        'legal_issues',
        'affective_fulfillment',
        'sexual_fulfillment',
        'consumption_pattern',
        'dependency_investment',
        'general_condition',
        'respiratory_signs',
        'neurological_signs',
        'psychiatric_disorders',
        'other_symptoms',
        'medical_surgical_history',
        'allergy_history',
        'psychiatric_history',
        'trauma_history',
        'psychological_assessment',
        'biological_assessment',
        'diagnostic_conclusion',
        'treatment_plan',
        'recommendations',
        'documents',
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'weight_loss' => 'boolean',
        'pale_complexion' => 'boolean',
        'withdrawal_insomnia' => 'boolean',
        'nightmares' => 'boolean',
        'hallucinations' => 'boolean',
        'somatic_disorders' => 'boolean',
        'behavioral_delirium' => 'boolean',
        'legal_issues' => 'boolean',
        'documents' => 'array',
    ];

    // Relations
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function treatments()
    {
        return $this->morphMany(Treatment::class, 'treatable');
    }

    // Méthode pour ajouter un document
    public function addDocument($filePath, $originalName, $type)
    {
        $documents = $this->documents ?? [];
        $documents[] = [
            'path' => $filePath,
            'original_name' => $originalName,
            'type' => $type,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->documents = $documents;
        $this->save();
    }

    // Méthode pour supprimer un document
    public function removeDocument($index)
    {
        $documents = $this->documents ?? [];
        if (isset($documents[$index])) {
            // Supprimer le fichier physique
            Storage::disk('public')->delete($documents[$index]['path']);
            array_splice($documents, $index, 1);
            $this->documents = $documents;
            $this->save();
        }
    }
}
