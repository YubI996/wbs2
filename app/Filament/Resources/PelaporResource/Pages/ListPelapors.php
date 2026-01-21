<?php

namespace App\Filament\Resources\PelaporResource\Pages;

use App\Filament\Resources\PelaporResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelapors extends ListRecords
{
    protected static string $resource = PelaporResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
