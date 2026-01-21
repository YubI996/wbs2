<?php

namespace App\Enums;

enum FileType: string
{
    case DOKUMEN = 'dokumen';
    case FOTO = 'foto';
    case LAINNYA = 'lainnya';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::DOKUMEN => 'Dokumen',
            self::FOTO => 'Foto/Gambar',
            self::LAINNYA => 'Lainnya',
        };
    }

    /**
     * Get allowed MIME types for each file type
     */
    public function allowedMimes(): array
    {
        return match($this) {
            self::DOKUMEN => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            self::FOTO => [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            self::LAINNYA => [],
        };
    }

    /**
     * Get allowed extensions for each file type
     */
    public function allowedExtensions(): array
    {
        return match($this) {
            self::DOKUMEN => ['pdf', 'doc', 'docx'],
            self::FOTO => ['jpg', 'jpeg', 'png', 'webp'],
            self::LAINNYA => [],
        };
    }

    /**
     * Determine file type from MIME type
     */
    public static function fromMime(string $mimeType): self
    {
        foreach (self::cases() as $type) {
            if (in_array($mimeType, $type->allowedMimes())) {
                return $type;
            }
        }
        return self::LAINNYA;
    }
}
