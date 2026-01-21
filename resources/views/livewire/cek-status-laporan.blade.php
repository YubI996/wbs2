<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-custom rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">WBS Kota Bontang</div>
                        <div class="text-xs text-gray-500">Cek Status Laporan</div>
                    </div>
                </a>
                
                <a href="{{ route('buat-laporan') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Laporan
                </a>
            </div>
        </div>
    </header>
    
    <main class="container mx-auto px-4 py-8 max-w-2xl">
        @if($aduan)
            <!-- Result View -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-custom p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-xl font-bold">{{ $aduan->nomor_registrasi }}</h1>
                        <button wire:click="resetSearch" class="text-blue-200 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold
                            {{ $aduan->status === \App\Enums\AduanStatus::SELESAI ? 'bg-green-500' : '' }}
                            {{ $aduan->status === \App\Enums\AduanStatus::DITOLAK ? 'bg-red-500' : '' }}
                            {{ $aduan->status === \App\Enums\AduanStatus::PENDING ? 'bg-gray-400' : '' }}
                            {{ in_array($aduan->status, [\App\Enums\AduanStatus::VERIFIKASI, \App\Enums\AduanStatus::PROSES, \App\Enums\AduanStatus::INVESTIGASI]) ? 'bg-yellow-500' : '' }}">
                            {{ $aduan->status->label() }}
                        </span>
                        <span class="text-blue-200 text-sm">
                            Diajukan {{ $aduan->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Info Laporan -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Informasi Laporan</h3>
                        <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Kategori</span>
                                <span class="font-medium text-gray-900">{{ $aduan->jenisAduan->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tanggal Lapor</span>
                                <span class="font-medium text-gray-900">{{ $aduan->created_at->format('d F Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Saluran</span>
                                <span class="font-medium text-gray-900">{{ $aduan->channel->label() }}</span>
                            </div>
                            @if($aduan->buktiPendukungs->count() > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Bukti</span>
                                <span class="font-medium text-gray-900">{{ $aduan->buktiPendukungs->count() }} file</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Timeline -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Riwayat Status</h3>
                        
                        @if($aduan->publicTimelines->count() > 0)
                        <div class="relative">
                            <div class="absolute left-4 top-6 bottom-6 w-0.5 bg-gray-200"></div>
                            
                            <div class="space-y-6">
                                @foreach($aduan->publicTimelines as $timeline)
                                <div class="relative flex gap-4">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 z-10
                                        {{ \App\Enums\AduanStatus::from($timeline->new_status) === \App\Enums\AduanStatus::SELESAI ? 'bg-green-500' : '' }}
                                        {{ \App\Enums\AduanStatus::from($timeline->new_status) === \App\Enums\AduanStatus::DITOLAK ? 'bg-red-500' : '' }}
                                        {{ \App\Enums\AduanStatus::from($timeline->new_status) === \App\Enums\AduanStatus::PENDING ? 'bg-gray-400' : '' }}
                                        {{ in_array(\App\Enums\AduanStatus::from($timeline->new_status), [\App\Enums\AduanStatus::VERIFIKASI, \App\Enums\AduanStatus::PROSES, \App\Enums\AduanStatus::INVESTIGASI]) ? 'bg-yellow-500' : '' }}">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 bg-gray-50 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-semibold text-gray-900">
                                                {{ \App\Enums\AduanStatus::from($timeline->new_status)->label() }}
                                            </span>
                                            <span class="text-gray-500 text-sm">
                                                {{ $timeline->created_at->format('d M Y H:i') }}
                                            </span>
                                        </div>
                                        @if($timeline->komentar)
                                        <p class="text-gray-600 text-sm">{{ $timeline->komentar }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            Belum ada riwayat status
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- Search Form -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Cek Status Laporan</h1>
                <p class="text-gray-500">Masukkan nomor registrasi dan password untuk melihat status laporan Anda</p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <form wire:submit.prevent="search" class="space-y-6">
                    <div>
                        <label for="nomor_registrasi" class="block text-sm font-medium text-gray-700 mb-2">Nomor Registrasi</label>
                        <input type="text" id="nomor_registrasi" wire:model="nomor_registrasi" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition uppercase"
                               placeholder="WBS-2026-XXXXX">
                        @error('nomor_registrasi') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" wire:model="password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Masukkan password">
                        @error('password') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    @if($error)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex gap-3">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-red-700">{{ $error }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <button type="submit"
                            class="w-full btn-primary inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold transition"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75">
                        <span wire:loading.remove wire:target="search">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="search">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="search">Cek Status</span>
                        <span wire:loading wire:target="search">Mencari...</span>
                    </button>
                </form>
            </div>
            
            <!-- Help Text -->
            <div class="mt-8 text-center">
                <p class="text-gray-500 text-sm">
                    Belum punya nomor registrasi? 
                    <a href="{{ route('buat-laporan') }}" class="text-blue-600 hover:text-blue-700 font-medium">Buat Laporan</a>
                </p>
            </div>
        @endif
    </main>
</div>
