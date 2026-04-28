<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait HandlesFileUploads
{
    /**
     * Upload un fichier pour un modèle spécifique
     */
    public function uploadFile(UploadedFile $file, $model, $serviceType, $subFolder = 'documents')
    {
        $path = $file->store("patients/{$model->patient_id}/{$serviceType}/{$subFolder}", 'public');

        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Supprime un fichier
     */
    public function deleteFile($path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Télécharge plusieurs fichiers
     */
    public function uploadMultipleFiles(array $files, $model, $serviceType, $subFolder = 'documents')
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFiles[] = $this->uploadFile($file, $model, $serviceType, $subFolder);
            }
        }

        return $uploadedFiles;
    }

    /**
     * Obtient l'URL publique d'un fichier
     */
    public function getFileUrl($path)
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Vérifie si un fichier existe
     */
    public function fileExists($path)
    {
        return Storage::disk('public')->exists($path);
    }
}
