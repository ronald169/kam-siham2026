<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
        'medical_record_number',
        'name',
        'date_of_birth',
        'age',
        'sex',
        'place_of_birth',
        'nationality',
        'tribe',
        'religion',
        'study_level',
        'profession',
        'marital_status',
        'patient_phone',
        'patient_email',
        'patient_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'family_dynamics',
        'type_of_family',
        'order_child',
        'childhood_antecedents',
        'medical_antecedents',
        'surgical_antecedents',
        'family_conflicts',
        'health_problems',
        'father_name',
        'father_age',
        'father_religion',
        'father_profession',
        'father_health_status',
        'father_education_level',
        'father_alive',
        'mother_name',
        'mother_age',
        'mother_religion',
        'mother_profession',
        'mother_health_status',
        'mother_education_level',
        'mother_alive',
        'parents_relationship',
        'siblings_relationship',
        'privileged_relationship',
        'frequency_stay_with_parents',
        'number_of_children_at_home',
        'children_list',
        'number_of_friends',
        'number_of_true_friends',
        'intimate_friends_quality',
        'social_relations_nature',
        'leisure_activities',
        'self_relationship',
        'self_perceived_problems',
        'self_judgment',
        'expectations_from_psychologist',
        'allergies',
        'current_treatments',
        'blood_type',
        'weight',
        'height',
        'status',
        'admission_date',
        'discharge_date',
        'discharge_reason',
        'referring_doctor_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'discharge_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Accesseur pour l'âge calculé automatiquement
    public function setAgeAttribute($value)
    {
        if ($this->date_of_birth) {
            $this->attributes['age'] = $this->date_of_birth->age;
        } else {
            $this->attributes['age'] = $value;
        }
    }

    public function getFormattedAdmissionDateAttribute()
    {
        return $this->admission_date ? Carbon::parse($this->admission_date)->format('d/m/Y') : '-';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : '-';
    }

    // Relations
    public function referringDoctor()
    {
        return $this->belongsTo(User::class, 'referring_doctor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function toxicologies()
    {
        return $this->hasMany(Toxicology::class);
    }

    public function psychopathologies()
    {
        return $this->hasMany(Psychopathology::class);
    }

    public function medecines()
    {
        return $this->hasMany(Medecine::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Méthode utilitaire pour obtenir la dernière consultation de chaque service
    public function lastToxicology()
    {
        return $this->toxicologies()->latest()->first();
    }

    public function lastPsychopathology()
    {
        return $this->psychopathologies()->latest()->first();
    }

    public function lastMedecine()
    {
        return $this->medecines()->latest()->first();
    }

    // Scope pour les patients actifs
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
