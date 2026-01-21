<?php

namespace App\Filament\Resources\PelaporResource\Pages;

use App\Filament\Resources\PelaporResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPelapor extends EditRecord
{
    protected static string $resource = PelaporResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
