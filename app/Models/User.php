<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'specialty', 'is_active',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        // Vérification des rôles
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMedecin(): bool
    {
        return $this->role === 'medecin';
    }

    public function isConsultant(): bool
    {
        return $this->role === 'consultant';
    }

    // Relations
    public function createdPatients()
    {
        return $this->hasMany(Patient::class, 'created_by');
    }

    public function referredPatients()
    {
        return $this->hasMany(Patient::class, 'referring_doctor_id');
    }

    public function toxicologies()
    {
        return $this->hasMany(Toxicology::class, 'doctor_id');
    }

    public function psychopathologies()
    {
        return $this->hasMany(Psychopathology::class, 'doctor_id');
    }

    public function medecines()
    {
        return $this->hasMany(Medecine::class, 'doctor_id');
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
}
