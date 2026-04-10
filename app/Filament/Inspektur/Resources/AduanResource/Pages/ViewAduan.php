<?php

namespace App\Filament\Inspektur\Resources\AduanResource\Pages;

use App\Enums\AduanStatus;
use App\Filament\Inspektur\Resources\AduanResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;

class ViewAduan extends ViewRecord
{
    protected static string $resource = AduanResource::class;

    public ?string $decryptedName = null;
    public ?string $decryptedPhone = null;
    public bool $isDecrypted = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('decrypt')
                ->label('Lihat Identitas Asli')
                ->icon('heroicon-o-lock-open')
                ->color('warning')
                ->visible(fn (): bool => ! $this->isDecrypted && ($this->record->pelapor?->is_anonim ?? false))
                ->modalHeading('Konfirmasi Akses Identitas')
                ->modalDescription('Masukkan password akun Anda untuk melihat identitas asli pelapor anonim.')
                ->form([
                    Forms\Components\TextInput::make('password')
                        ->label('Password Akun Anda')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data, Actions\Action $action): void {
                    if (! Hash::check($data['password'], auth()->user()->password)) {
                        Notification::make()
                            ->danger()
                            ->title('Password salah')
                            ->send();
                        $action->halt();

                        return;
                    }

                    $identity = $this->record->pelapor?->decryptIdentity();

                    if ($identity) {
                        $this->decryptedName = $identity['nama'] ?? null;
                        $this->decryptedPhone = $identity['phone'] ?? null;
                        $this->isDecrypted = true;

                        Notification::make()
                            ->success()
                            ->title('Identitas berhasil didekripsi')
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title('Data identitas tidak ditemukan')
                            ->send();
                    }
                }),

            Actions\Action::make('encrypt')
                ->label('Sembunyikan Identitas')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->visible(fn (): bool => $this->isDecrypted)
                ->action(function (): void {
                    $this->decryptedName = null;
                    $this->decryptedPhone = null;
                    $this->isDecrypted = false;
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
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
                        Infolists\Components\TextEntry::make('nama_pelapor')
                            ->label('Nama')
                            ->state(fn ($record): string => $this->decryptedName
                                ?? ($record->pelapor?->display_name ?? '-'))
                            ->helperText(fn ($record): ?string => $record->pelapor?->is_anonim && $this->isDecrypted
                                ? 'Identitas asli (terdekripsi)'
                                : null)
                            ->color(fn ($record): ?string => $record->pelapor?->is_anonim && $this->isDecrypted
                                ? 'warning'
                                : null),
                        Infolists\Components\TextEntry::make('phone_pelapor')
                            ->label('Telepon')
                            ->state(fn ($record): string => $this->decryptedPhone
                                ?? ($record->pelapor?->phone ?? '-'))
                            ->helperText(fn ($record): ?string => $record->pelapor?->is_anonim && $this->isDecrypted
                                ? 'Identitas asli (terdekripsi)'
                                : null)
                            ->color(fn ($record): ?string => $record->pelapor?->is_anonim && $this->isDecrypted
                                ? 'warning'
                                : null),
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
}
