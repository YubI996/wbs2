<?php

namespace App\Filament\Resources\AduanResource\RelationManagers;

use App\Enums\AduanStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TimelinesRelationManager extends RelationManager
{
    protected static string $relationship = 'timelines';
    
    protected static ?string $title = 'Riwayat Status';
    
    protected static ?string $modelLabel = 'Timeline';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('new_status')
                    ->label('Status')
                    ->options(AduanStatus::options())
                    ->required()
                    ->native(false),
                Forms\Components\Textarea::make('komentar')
                    ->label('Komentar')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_public')
                    ->label('Tampilkan ke Pelapor')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('new_status')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('old_status')
                    ->label('Status Lama')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => $state 
                        ? AduanStatus::from($state)->label() 
                        : '-'),
                Tables\Columns\TextColumn::make('new_status')
                    ->label('Status Baru')
                    ->badge()
                    ->color(fn (string $state): string => AduanStatus::from($state)->color())
                    ->formatStateUsing(fn (string $state): string => AduanStatus::from($state)->label()),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh')
                    ->default('Sistem'),
                Tables\Columns\TextColumn::make('komentar')
                    ->label('Komentar')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // No create action - timeline is created via updateStatus
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
