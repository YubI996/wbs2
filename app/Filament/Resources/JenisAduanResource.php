<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisAduanResource\Pages;
use App\Models\JenisAduan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class JenisAduanResource extends Resource
{
    protected static ?string $model = JenisAduan::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'Manajemen';
    
    protected static ?string $modelLabel = 'Kategori Aduan';
    
    protected static ?string $pluralModelLabel = 'Kategori Aduan';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Kode')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Jika tidak aktif, kategori tidak akan muncul di form pengaduan'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('Kode')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('aduans_count')
                    ->label('Jumlah Laporan')
                    ->counts('aduans')
                    ->sortable(),
            ])
            ->defaultSort('slug')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function () {
                        // Clear cache when updating
                        Cache::forget('jenis_aduans_active');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            Cache::forget('jenis_aduans_active');
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJenisAduans::route('/'),
            'create' => Pages\CreateJenisAduan::route('/create'),
            'edit' => Pages\EditJenisAduan::route('/{record}/edit'),
        ];
    }
    
    /**
     * Get cached active jenis aduans for forms
     */
    public static function getCachedOptions(): array
    {
        return Cache::remember('jenis_aduans_active', 3600, function () {
            return JenisAduan::active()
                ->pluck('name', 'slug')
                ->toArray();
        });
    }
}
