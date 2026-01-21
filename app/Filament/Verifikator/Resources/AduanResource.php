<?php

namespace App\Filament\Verifikator\Resources;

use App\Enums\AduanStatus;
use App\Enums\ReportChannel;
use App\Filament\Verifikator\Resources\AduanResource\Pages;
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

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
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
                                AduanStatus::PENDING->value => AduanStatus::PENDING->label(),
                                AduanStatus::VERIFIKASI->value => AduanStatus::VERIFIKASI->label(),
                                AduanStatus::PROSES->value => AduanStatus::PROSES->label(),
                                AduanStatus::DITOLAK->value => AduanStatus::DITOLAK->label(),
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
                        AduanStatus::PENDING->value => AduanStatus::PENDING->label(),
                        AduanStatus::VERIFIKASI->value => AduanStatus::VERIFIKASI->label(),
                        AduanStatus::PROSES->value => AduanStatus::PROSES->label(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Aduan $record) => $record->status === AduanStatus::PENDING)
                    ->action(function (Aduan $record) {
                        $record->updateStatus(
                            AduanStatus::VERIFIKASI,
                            'Laporan sedang diverifikasi',
                            auth()->user(),
                            true
                        );
                        
                        \App\Jobs\SendStatusUpdateEmail::dispatch($record, AduanStatus::VERIFIKASI, 'Laporan Anda sedang diverifikasi oleh tim kami.');
                        \Illuminate\Support\Facades\Cache::forget('admin_stats');
                    }),
                Tables\Actions\Action::make('process')
                    ->label('Proses')
                    ->icon('heroicon-o-cog')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Aduan $record) => $record->status === AduanStatus::VERIFIKASI)
                    ->action(function (Aduan $record) {
                        $record->updateStatus(
                            AduanStatus::PROSES,
                            'Laporan dalam proses penanganan',
                            auth()->user(),
                            true
                        );
                        
                        \App\Jobs\SendStatusUpdateEmail::dispatch($record, AduanStatus::PROSES, 'Laporan Anda sedang dalam proses penanganan.');
                        \Illuminate\Support\Facades\Cache::forget('admin_stats');
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('alasan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (Aduan $record) => in_array($record->status, [AduanStatus::PENDING, AduanStatus::VERIFIKASI]))
                    ->action(function (Aduan $record, array $data) {
                        $record->updateStatus(
                            AduanStatus::DITOLAK,
                            $data['alasan'],
                            auth()->user(),
                            true
                        );
                        
                        \App\Jobs\SendStatusUpdateEmail::dispatch($record, AduanStatus::DITOLAK, $data['alasan']);
                        \Illuminate\Support\Facades\Cache::forget('admin_stats');
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
                    ]),
                    
                Infolists\Components\Section::make('Isi Laporan')
                    ->schema([
                        Infolists\Components\TextEntry::make('identitas_terlapor')
                            ->label('Terlapor')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('what')
                            ->label('Apa yang terjadi')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('kronologis_lengkap')
                            ->label('Kronologis Lengkap')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
                AduanStatus::PENDING,
                AduanStatus::VERIFIKASI,
                AduanStatus::PROSES,
            ]);
    }
}
