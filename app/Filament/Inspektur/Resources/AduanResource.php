<?php

namespace App\Filament\Inspektur\Resources;

use App\Enums\AduanStatus;
use App\Filament\Inspektur\Resources\AduanResource\Pages;
use App\Models\Aduan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AduanResource extends Resource
{
    protected static ?string $model = Aduan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    
    protected static ?string $modelLabel = 'Laporan';
    
    protected static ?string $pluralModelLabel = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Laporan')
                            ->options([
                                AduanStatus::PROSES->value => AduanStatus::PROSES->label(),
                                AduanStatus::INVESTIGASI->value => AduanStatus::INVESTIGASI->label(),
                                AduanStatus::SELESAI->value => AduanStatus::SELESAI->label(),
                            ])
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_registrasi')
                    ->label('No. Registrasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reporter_name')
                    ->label('Pelapor'),
                Tables\Columns\TextColumn::make('jenisAduan.name')
                    ->label('Kategori')
                    ->wrap()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (AduanStatus $state): string => $state->color())
                    ->formatStateUsing(fn (AduanStatus $state): string => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Lapor')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        AduanStatus::PROSES->value => AduanStatus::PROSES->label(),
                        AduanStatus::INVESTIGASI->value => AduanStatus::INVESTIGASI->label(),
                        AduanStatus::SELESAI->value => AduanStatus::SELESAI->label(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('investigate')
                    ->label('Investigasi')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Aduan $record) => $record->status === AduanStatus::PROSES)
                    ->action(function (Aduan $record) {
                        $record->updateStatus(
                            AduanStatus::INVESTIGASI,
                            'Laporan sedang dalam investigasi',
                            auth()->user(),
                            true
                        );
                        
                        \App\Jobs\SendStatusUpdateEmail::dispatch($record, AduanStatus::INVESTIGASI, 'Laporan Anda sedang dalam proses investigasi.');
                        \Illuminate\Support\Facades\Cache::forget('admin_stats');
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('kesimpulan')
                            ->label('Kesimpulan Akhir')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(fn (Aduan $record) => in_array($record->status, [AduanStatus::PROSES, AduanStatus::INVESTIGASI]))
                    ->action(function (Aduan $record, array $data) {
                        $record->updateStatus(
                            AduanStatus::SELESAI,
                            $data['kesimpulan'],
                            auth()->user(),
                            true
                        );
                        
                        \App\Jobs\SendStatusUpdateEmail::dispatch($record, AduanStatus::SELESAI, $data['kesimpulan']);
                        \Illuminate\Support\Facades\Cache::forget('admin_stats');
                        \Illuminate\Support\Facades\Cache::forget('landing_stats');
                    }),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Infolists\Components\TextEntry::make('nomor_registrasi')
                            ->label('Nomor Registrasi')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (AduanStatus $state): string => $state->color())
                            ->formatStateUsing(fn (AduanStatus $state): string => $state->label()),
                        Infolists\Components\TextEntry::make('jenisAduan.name')
                            ->label('Kategori'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Lapor')
                            ->dateTime('d F Y H:i'),
                    ])->columns(4),
                    
                Infolists\Components\Section::make('Pelapor')
                    ->schema([
                        Infolists\Components\TextEntry::make('reporter_name')
                            ->label('Nama'),
                        Infolists\Components\TextEntry::make('pelapor.phone')
                            ->label('Telepon')
                            ->default('-'),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Isi Laporan')
                    ->schema([
                        Infolists\Components\TextEntry::make('identitas_terlapor')
                            ->label('Terlapor')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('what')
                            ->label('Apa yang terjadi')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('who')
                            ->label('Siapa yang terlibat')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('when_date')
                            ->label('Kapan')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('lokasi_kejadian')
                            ->label('Lokasi'),
                        Infolists\Components\TextEntry::make('where_location')
                            ->label('Di mana')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('why')
                            ->label('Mengapa')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('how')
                            ->label('Bagaimana')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAduans::route('/'),
            'view' => Pages\ViewAduan::route('/{record}'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pelapor', 'jenisAduan'])
            ->whereIn('status', [
                AduanStatus::PROSES,
                AduanStatus::INVESTIGASI,
                AduanStatus::SELESAI,
            ]);
    }
}
