<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Medecine extends Model
{
    use HasFactory;

    protected $table = 'medecines';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_date',
        'status',
        'consultation_reason',
        'illness_history',
        'medical_history',
        'physical_examination',
        'diagnostic_hypothesis',
        'requested_exams',
        'exam_results',
        'prescribed_treatments',
        'medical_prescriptions',
        'next_appointment',
        'follow_up_instructions',
        'documents',
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'next_appointment' => 'date',
        'exam_results' => 'array',
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

    // Méthodes de gestion de documents
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

    public function addExamResult($examName, $filePath, $originalName)
    {
        $examResults = $this->exam_results ?? [];
        $examResults[] = [
            'exam_name' => $examName,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->exam_results = $examResults;
        $this->save();
    }
}
