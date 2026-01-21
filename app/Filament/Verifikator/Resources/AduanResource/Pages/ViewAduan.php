<?php

namespace App\Filament\Verifikator\Resources\AduanResource\Pages;

use App\Filament\Verifikator\Resources\AduanResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAduan extends ViewRecord
{
    protected static string $resource = AduanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
