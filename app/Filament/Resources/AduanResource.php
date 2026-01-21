<?php

namespace App\Filament\Resources;

use App\Enums\AduanStatus;
use App\Enums\ReportChannel;
use App\Filament\Resources\AduanResource\Pages;
use App\Filament\Resources\AduanResource\RelationManagers;
use App\Models\Aduan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AduanResource extends Resource
{
    protected static ?string $model = Aduan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Pengaduan';
    
    protected static ?string $modelLabel = 'Laporan';
    
    protected static ?string $pluralModelLabel = 'Laporan';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pelapor')
                    ->schema([
                        Forms\Components\Placeholder::make('pelapor_info')
                            ->label('Pelapor')
                            ->content(fn (Aduan $record): string => $record->reporter_name ?? '-'),
                        Forms\Components\Placeholder::make('nomor_registrasi_display')
                            ->label('Nomor Registrasi')
                            ->content(fn (Aduan $record): string => $record->nomor_registrasi ?? '-'),
                        Forms\Components\Placeholder::make('channel_display')
                            ->label('Saluran')
                            ->content(fn (Aduan $record): string => $record->channel?->label() ?? '-'),
                    ])->columns(3)
                    ->visibleOn('edit'),
                    
                Forms\Components\Section::make('Kategori & Terlapor')
                    ->schema([
                        Forms\Components\Select::make('jenis_aduan_id')
                            ->label('Kategori Laporan')
                            ->relationship('jenisAduan', 'name')
                            ->required()
                            ->native(false)
                            ->preload(),
                        Forms\Components\Textarea::make('identitas_terlapor')
                            ->label('Identitas Terlapor')
                            ->helperText('Nama dan jabatan pihak yang dilaporkan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Kronologis Kejadian (5W + 1H)')
                    ->schema([
                        Forms\Components\Textarea::make('what')
                            ->label('Apa yang terjadi? (What)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('who')
                            ->label('Siapa yang terlibat? (Who)')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('when_date')
                            ->label('Kapan terjadi? (When)')
                            ->native(false),
                        Forms\Components\TextInput::make('lokasi_kejadian')
                            ->label('Lokasi Kejadian'),
                        Forms\Components\Textarea::make('where_location')
                            ->label('Di mana? (Where)')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('why')
                            ->label('Mengapa terjadi? (Why)')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('how')
                            ->label('Bagaimana kronologisnya? (How)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Laporan')
                            ->options(AduanStatus::options())
                            ->required()
                            ->native(false),
                    ])->columns(1),
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
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reporter_name')
                    ->label('Pelapor')
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->whereHas('pelapor', function ($q) use ($search) {
                            $q->where('nama', 'like', "%{$search}%");
                        })->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('jenisAduan.name')
                    ->label('Kategori')
                    ->searchable()
                    ->wrap()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (AduanStatus $state): string => $state->color())
                    ->formatStateUsing(fn (AduanStatus $state): string => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel')
                    ->label('Saluran')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (ReportChannel $state): string => $state->label())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Lapor')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(AduanStatus::options()),
                Tables\Filters\SelectFilter::make('jenis_aduan_id')
                    ->label('Kategori')
                    ->relationship('jenisAduan', 'name'),
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Saluran')
                    ->options(ReportChannel::options()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('new_status')
                            ->label('Status Baru')
                            ->options(AduanStatus::options())
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('komentar')
                            ->label('Komentar')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Tampilkan ke Pelapor')
                            ->default(true),
                    ])
                    ->action(function (Aduan $record, array $data) {
                        $newStatus = AduanStatus::from($data['new_status']);
                        $komentar = $data['komentar'] ?? null;
                        
                        $record->updateStatus(
                            $newStatus,
                            $komentar,
                            auth()->user(),
                            $data['is_public']
                        );
                        
                        // Dispatch email notification if public timeline
                        if ($data['is_public']) {
                            \App\Jobs\SendStatusUpdateEmail::dispatch($record, $newStatus, $komentar);
                        }
                        
                        // Clear cache
                        \Illuminate\Support\Facades\Cache::forget('admin_stats');
                        \Illuminate\Support\Facades\Cache::forget('landing_stats');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Infolists\Components\TextEntry::make('nomor_registrasi')
                            ->label('Nomor Registrasi')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (AduanStatus $state): string => $state->color())
                            ->formatStateUsing(fn (AduanStatus $state): string => $state->label()),
                        Infolists\Components\TextEntry::make('channel')
                            ->label('Saluran')
                            ->formatStateUsing(fn (ReportChannel $state): string => $state->label()),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Lapor')
                            ->dateTime('d F Y H:i'),
                    ])->columns(4),
                    
                Infolists\Components\Section::make('Pelapor')
                    ->schema([
                        Infolists\Components\TextEntry::make('reporter_name')
                            ->label('Nama Pelapor'),
                        Infolists\Components\TextEntry::make('pelapor.phone')
                            ->label('No. HP')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('pelapor.email')
                            ->label('Email')
                            ->default('-'),
                    ])->columns(3),
                    
                Infolists\Components\Section::make('Kategori & Terlapor')
                    ->schema([
                        Infolists\Components\TextEntry::make('jenisAduan.name')
                            ->label('Kategori'),
                        Infolists\Components\TextEntry::make('identitas_terlapor')
                            ->label('Identitas Terlapor')
                            ->columnSpanFull(),
                    ]),
                    
                Infolists\Components\Section::make('Kronologis')
                    ->schema([
                        Infolists\Components\TextEntry::make('what')
                            ->label('Apa yang terjadi?')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('who')
                            ->label('Siapa yang terlibat?')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('when_date')
                            ->label('Kapan?')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('lokasi_kejadian')
                            ->label('Lokasi'),
                        Infolists\Components\TextEntry::make('where_location')
                            ->label('Di mana?')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('why')
                            ->label('Mengapa?')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('how')
                            ->label('Bagaimana?')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BuktiPendukungsRelationManager::class,
            RelationManagers\TimelinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAduans::route('/'),
            'create' => Pages\CreateAduan::route('/create'),
            'view' => Pages\ViewAduan::route('/{record}'),
            'edit' => Pages\EditAduan::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pelapor', 'user', 'jenisAduan']) // Eager loading for performance
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
