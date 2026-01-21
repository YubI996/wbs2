<?php

namespace App\Filament\Resources\JenisAduanResource\Pages;

use App\Filament\Resources\JenisAduanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisAduans extends ListRecords
{
    protected static string $resource = JenisAduanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
