<?php

namespace App\Filament\Inspektur\Resources\AduanResource\Pages;

use App\Filament\Inspektur\Resources\AduanResource;
use Filament\Resources\Pages\ListRecords;

class ListAduans extends ListRecords
{
    protected static string $resource = AduanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
