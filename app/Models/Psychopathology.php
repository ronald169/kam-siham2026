<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Psychopathology extends Model
{
    use HasFactory;

    protected $table = 'psychopathologies';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_date',
        'status',
        'chief_complaint',
        'illness_history',
        'appearance',
        'facial_expressions',
        'gait',
        'eye_contact',
        'other_presentation',
        'contact_quality',
        'agitation',
        'impulses',
        'stupor',
        'catalepsy',
        'tics',
        'other_behaviors',
        'insomnia',
        'daytime_sleepiness',
        'hypersomnia',
        'dream_disturbances',
        'other_sleep_issues',
        'food_restriction',
        'food_refusal',
        'excessive_eating',
        'excessive_drinking',
        'other_eating_behaviors',
        'sexual_orientation',
        'sexual_activity_frequency',
        'masturbation',
        'impotence',
        'other_sexual_issues',
        'suicidal_ideation',
        'suicide_attempts',
        'suicidal_equivalents',
        'runaway',
        'pathological_stealing',
        'sexual_offenses',
        'other_social_conduct_disorders',
        'alcoholism',
        'smoking',
        'other_addictions',
        'speech_disorders',
        'memory_disorders',
        'thought_flow_disorders',
        'thought_content_disorders',
        'global_thought_distortion',
        'judgment_disorders',
        'hallucinations',
        'attention_quality',
        'spatiotemporal_orientation',
        'hypovigilance_hypervigilance',
        'twilight_states',
        'oniric_states',
        'other_consciousness_disorders',
        'affect_expression_disorders',
        'mood_disorders',
        'vital_signs',
        'general_condition',
        'cardiovascular_exam',
        'pulmonary_exam',
        'neurological_exam',
        'clinical_conclusion',
        'diagnostic_discussion',
        'psychological_assesment_summary',
        'treatment_recommendations',
        'documents',
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'agitation' => 'boolean',
        'impulses' => 'boolean',
        'stupor' => 'boolean',
        'catalepsy' => 'boolean',
        'tics' => 'boolean',
        'insomnia' => 'boolean',
        'daytime_sleepiness' => 'boolean',
        'hypersomnia' => 'boolean',
        'dream_disturbances' => 'boolean',
        'food_restriction' => 'boolean',
        'food_refusal' => 'boolean',
        'excessive_eating' => 'boolean',
        'excessive_drinking' => 'boolean',
        'masturbation' => 'boolean',
        'impotence' => 'boolean',
        'suicidal_ideation' => 'boolean',
        'suicide_attempts' => 'boolean',
        'suicidal_equivalents' => 'boolean',
        'runaway' => 'boolean',
        'pathological_stealing' => 'boolean',
        'sexual_offenses' => 'boolean',
        'alcoholism' => 'boolean',
        'smoking' => 'boolean',
        'hypovigilance_hypervigilance' => 'boolean',
        'twilight_states' => 'boolean',
        'oniric_states' => 'boolean',
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

    // Méthodes de gestion de documents (similaires à Toxicology)
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

    public function removeDocument($index)
    {
        $documents = $this->documents ?? [];
        if (isset($documents[$index])) {
            Storage::disk('public')->delete($documents[$index]['path']);
            array_splice($documents, $index, 1);
            $this->documents = $documents;
            $this->save();
        }
    }
}
