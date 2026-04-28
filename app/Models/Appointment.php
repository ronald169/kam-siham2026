<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_datetime',
        'service_type',
        'status',
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_datetime' => 'datetime',
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

    // Scope pour les rendez-vous d'aujourd'hui
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_datetime', today());
    }

    // Scope pour les rendez-vous à venir
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_datetime', '>', now())
                     ->where('status', 'scheduled');
    }

    // Scope pour les rendez-vous passés
    public function scopePast($query)
    {
        return $query->where('appointment_datetime', '<', now());
    }
}
