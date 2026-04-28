<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatable_type',
        'treatable_id',
        'patient_id',
        'doctor_id',
        'treatment_date',
        'treatment_time',
        'observations',
        'medications_given',
        'care_provided',
        'cost',
        'patient_condition',
        'doctor_notes',
        'next_instructions',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'treatment_time' => 'datetime',
        'cost' => 'decimal:2',
    ];

    // Relation polymorphique
    public function treatable()
    {
        return $this->morphTo();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // Scope pour les traitements d'aujourd'hui
    public function scopeToday($query)
    {
        return $query->whereDate('treatment_date', today());
    }

    // Scope par service
    public function scopeForService($query, $serviceType)
    {
        return $query->where('treatable_type', 'App\\Models\\' . ucfirst($serviceType));
    }
}
