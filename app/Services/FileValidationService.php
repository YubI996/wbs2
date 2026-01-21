<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class FileValidationService
{
    /**
     * Allowed MIME types with their valid extensions
     */
    protected array $allowedMimeTypes = [
        // Images
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        
        // Documents
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'text/plain' => ['txt'],
        
        // Videos
        'video/mp4' => ['mp4'],
        'video/quicktime' => ['mov'],
        'video/x-msvideo' => ['avi'],
        'video/x-matroska' => ['mkv'],
        'video/webm' => ['webm'],
        
        // Audio
        'audio/mpeg' => ['mp3'],
        'audio/mp4' => ['m4a'],
        'audio/wav' => ['wav'],
        'audio/x-wav' => ['wav'],
    ];

    /**
     * Maximum file size in bytes (10 MB)
     */
    protected int $maxFileSize = 10485760;

    /**
     * Validate uploaded file for security
     * Detects fake extensions by checking actual MIME type
     *
     * @throws ValidationException
     */
    public function validate(UploadedFile $file, string $fieldName = 'file'): array
    {
        // Check file is valid
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                $fieldName => 'File tidak valid atau rusak.',
            ]);
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw ValidationException::withMessages([
                $fieldName => 'Ukuran file maksimal 10 MB.',
            ]);
        }

        // Get real MIME type using finfo (not from extension!)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMimeType = $finfo->file($file->getPathname());

        // Get extension from original filename
        $extension = strtolower($file->getClientOriginalExtension());

        // Check if MIME type is allowed
        if (!isset($this->allowedMimeTypes[$realMimeType])) {
            throw ValidationException::withMessages([
                $fieldName => 'Tipe file tidak diizinkan. MIME type: ' . $realMimeType,
            ]);
        }

        // Check if extension matches the MIME type (detect fake extension!)
        $validExtensions = $this->allowedMimeTypes[$realMimeType];
        if (!in_array($extension, $validExtensions)) {
            throw ValidationException::withMessages([
                $fieldName => 'Ekstensi file tidak sesuai dengan isi file (ekstensi palsu terdeteksi). ' .
                             'Ekstensi: .' . $extension . ', Tipe sebenarnya: ' . $realMimeType,
            ]);
        }

        return [
            'mime_type' => $realMimeType,
            'extension' => $extension,
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Get file type category from MIME type
     */
    public function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        return 'document';
    }

    /**
     * Generate unique filename for storage
     */
    public function generateFilename(UploadedFile $file, ?int $userId = null): string
    {
        $timestamp = now()->format('YmdHis');
        $random = \Illuminate\Support\Str::random(8);
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = \Illuminate\Support\Str::slug(substr($originalName, 0, 30));
        
        $prefix = $userId ? "{$userId}_" : '';
        
        return "{$prefix}{$timestamp}_{$random}_{$sanitizedName}.{$extension}";
    }
}
