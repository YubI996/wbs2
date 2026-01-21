<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">WBS Kota Bontang</div>
                        <div class="text-xs text-gray-500">Buat Laporan</div>
                    </div>
                </a>
                
                @if(!$submitted)
                <div class="text-sm text-gray-500">
                    Langkah {{ $step }} dari {{ $totalSteps }}
                </div>
                @endif
            </div>
        </div>
    </header>
    
    <main class="container mx-auto px-4 py-8 max-w-3xl">
        @if($submitted)
            <!-- Success State -->
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Laporan Berhasil Dikirim!</h1>
                <p class="text-gray-500 mb-8">Simpan informasi berikut untuk memantau status laporan Anda</p>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <div class="grid gap-4">
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">Nomor Registrasi</label>
                            <div class="text-2xl font-bold text-blue-600">{{ $nomor_registrasi }}</div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">Password Tracking</label>
                            <div class="text-2xl font-mono font-bold text-gray-900">{{ $tracking_password }}</div>
                        </div>
                    </div>
                    
                    <div class="flex gap-4 mt-6 justify-center">
                        <button type="button"
                                x-data="{ copied: false }"
                                x-on:click="
                                    const text = '{{ $nomor_registrasi }} | {{ $tracking_password }}';
                                    if (navigator.clipboard) {
                                        navigator.clipboard.writeText(text).then(() => {
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        }).catch(() => {
                                            // Fallback for non-HTTPS
                                            const el = document.createElement('textarea');
                                            el.value = text;
                                            el.style.position = 'absolute';
                                            el.style.left = '-9999px';
                                            document.body.appendChild(el);
                                            el.select();
                                            document.execCommand('copy');
                                            document.body.removeChild(el);
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        });
                                    } else {
                                        // Fallback for older browsers
                                        const el = document.createElement('textarea');
                                        el.value = text;
                                        el.style.position = 'absolute';
                                        el.style.left = '-9999px';
                                        document.body.appendChild(el);
                                        el.select();
                                        document.execCommand('copy');
                                        document.body.removeChild(el);
                                        copied = true;
                                        setTimeout(() => copied = false, 2000);
                                    }
                                "
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg transition"
                                :class="copied ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'">
                            <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <svg x-show="copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="copied ? 'Tersalin!' : 'Salin Semua'"></span>
                        </button>
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-8">
                    <div class="flex gap-3">
                        <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-semibold text-yellow-800">Penting!</div>
                            <div class="text-sm text-yellow-700">Simpan nomor registrasi dan password ini. Anda membutuhkannya untuk memantau status laporan.</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('cek-status') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cek Status Laporan
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        @else
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between mb-2">
                    @for($i = 1; $i <= $totalSteps; $i++)
                        <button wire:click="goToStep({{ $i }})" 
                                class="flex items-center justify-center w-10 h-10 rounded-full font-semibold transition
                                {{ $i < $step ? 'bg-green-500 text-white' : '' }}
                                {{ $i === $step ? 'bg-blue-600 text-white' : '' }}
                                {{ $i > $step ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'cursor-pointer' }}"
                                @if($i > $step) disabled @endif>
                            @if($i < $step)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </button>
                    @endfor
                </div>
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 transition-all duration-300" style="width: {{ (($step - 1) / ($totalSteps - 1)) * 100 }}%"></div>
                </div>
            </div>
            
            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <form wire:submit.prevent="{{ $step === $totalSteps ? 'submit' : 'nextStep' }}">
                    <!-- Step 1: Identitas -->
                    @if($step === 1)
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-900">Identitas Pelapor</h2>
                            <p class="text-gray-500 text-sm mt-1">Lengkapi data diri Anda</p>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" id="nama" wire:model="nama" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="Masukkan nama lengkap">
                                @error('nama') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">No. Handphone <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" wire:model="phone" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="08xxxxxxxxxx">
                                @error('phone') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="space-y-4">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_anonim" class="w-5 h-5 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="font-medium text-gray-900">Rahasiakan identitas saya (Anonim)</div>
                                        <div class="text-sm text-gray-500">Identitas Anda akan dienkripsi dan tidak ditampilkan ke pengelola</div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="notify_email" class="w-5 h-5 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="font-medium text-gray-900">Terima notifikasi via email</div>
                                        <div class="text-sm text-gray-500">Dapatkan update status laporan melalui email</div>
                                    </div>
                                </label>
                            </div>
                            
                            @if($notify_email)
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="email" wire:model="email" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="email@example.com">
                                @error('email') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Step 2: Kategori & Terlapor -->
                    @if($step === 2)
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-900">Substansi Laporan</h2>
                            <p class="text-gray-500 text-sm mt-1">Pilih kategori dan identifikasi pihak terlapor</p>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="jenis_aduan_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori Laporan <span class="text-red-500">*</span></label>
                                <select id="jenis_aduan_id" wire:model="jenis_aduan_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Pilih kategori...</option>
                                    @foreach($jenisAduanOptions as $slug => $name)
                                        <option value="{{ $slug }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('jenis_aduan_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="identitas_terlapor" class="block text-sm font-medium text-gray-700 mb-2">Identitas Terlapor <span class="text-red-500">*</span></label>
                                <textarea id="identitas_terlapor" wire:model="identitas_terlapor" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                          placeholder="Nama dan jabatan pihak yang dilaporkan"></textarea>
                                @error('identitas_terlapor') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                    
                    <!-- Step 3: Kronologis 5W+1H -->
                    @if($step === 3)
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-900">Kronologis Kejadian (5W + 1H)</h2>
                            <p class="text-gray-500 text-sm mt-1">Jelaskan kejadian secara lengkap</p>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="what" class="block text-sm font-medium text-gray-700 mb-2">Apa yang terjadi? (What) <span class="text-red-500">*</span></label>
                                <textarea id="what" wire:model="what" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                          placeholder="Jelaskan apa yang terjadi secara detail"></textarea>
                                @error('what') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="who" class="block text-sm font-medium text-gray-700 mb-2">Siapa yang terlibat? (Who)</label>
                                <textarea id="who" wire:model="who" rows="2"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                          placeholder="Sebutkan pihak-pihak yang terlibat"></textarea>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label for="when_date" class="block text-sm font-medium text-gray-700 mb-2">Kapan terjadi? (When)</label>
                                    <input type="date" id="when_date" wire:model="when_date" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                </div>
                                
                                <div>
                                    <label for="lokasi_kejadian" class="block text-sm font-medium text-gray-700 mb-2">Lokasi Kejadian</label>
                                    <input type="text" id="lokasi_kejadian" wire:model="lokasi_kejadian" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                           placeholder="Alamat atau nama tempat">
                                </div>
                            </div>
                            
                            <div>
                                <label for="where_location" class="block text-sm font-medium text-gray-700 mb-2">Di mana kejadian? (Where)</label>
                                <textarea id="where_location" wire:model="where_location" rows="2"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                          placeholder="Jelaskan lokasi kejadian secara spesifik"></textarea>
                            </div>
                            
                            <div>
                                <label for="why" class="block text-sm font-medium text-gray-700 mb-2">Mengapa terjadi? (Why)</label>
                                <textarea id="why" wire:model="why" rows="2"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                          placeholder="Apa dugaan penyebab atau motivasi"></textarea>
                            </div>
                            
                            <div>
                                <label for="how" class="block text-sm font-medium text-gray-700 mb-2">Bagaimana kronologinya? (How)</label>
                                <textarea id="how" wire:model="how" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                          placeholder="Jelaskan urutan kejadian dari awal hingga akhir"></textarea>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Step 4: Upload Bukti -->
                    @if($step === 4)
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-900">Upload Bukti Pendukung</h2>
                            <p class="text-gray-500 text-sm mt-1">Lampirkan dokumen atau foto sebagai bukti (opsional)</p>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">File Bukti</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition cursor-pointer"
                                     onclick="document.getElementById('file-upload').click()">
                                    <input type="file" id="file-upload" wire:model="bukti_files" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" class="hidden">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 font-medium">Klik untuk upload atau seret file ke sini</p>
                                    <p class="text-gray-400 text-sm mt-2">PDF, DOC, DOCX, JPG, PNG, WEBP (Maks. 10MB per file)</p>
                                </div>
                                
                                <div wire:loading wire:target="bukti_files" class="mt-4 text-center">
                                    <div class="inline-flex items-center gap-2 text-blue-600">
                                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Mengupload file...
                                    </div>
                                </div>
                                
                                @error('bukti_files.*') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                            </div>
                            
                            @if(count($bukti_files) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">File yang diupload ({{ count($bukti_files) }})</label>
                                <ul class="space-y-2">
                                    @foreach($bukti_files as $index => $file)
                                    <li class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <div>
                                                <div class="font-medium text-gray-900 text-sm">{{ $file->getClientOriginalName() }}</div>
                                                <div class="text-gray-500 text-xs">{{ number_format($file->getSize() / 1024, 1) }} KB</div>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="removeFile({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Step 5: Preview & Submit -->
                    @if($step === 5)
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-900">Preview & Konfirmasi</h2>
                            <p class="text-gray-500 text-sm mt-1">Periksa kembali data sebelum mengirim</p>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Pelapor -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h3 class="font-semibold text-gray-900 mb-3">Identitas Pelapor</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Nama:</span>
                                        <span class="font-medium ml-2">{{ $is_anonim ? 'Anonim' : $nama }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">No. HP:</span>
                                        <span class="font-medium ml-2">{{ $is_anonim ? '**********' : $phone }}</span>
                                    </div>
                                    @if($email)
                                    <div>
                                        <span class="text-gray-500">Email:</span>
                                        <span class="font-medium ml-2">{{ $email }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Laporan -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h3 class="font-semibold text-gray-900 mb-3">Substansi Laporan</h3>
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <span class="text-gray-500">Kategori:</span>
                                        <span class="font-medium ml-2">{{ $jenisAduanOptions[$jenis_aduan_id] ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block mb-1">Terlapor:</span>
                                        <span class="font-medium">{{ $identitas_terlapor }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block mb-1">Apa yang terjadi:</span>
                                        <span class="font-medium">{{ \Illuminate\Support\Str::limit($what, 200) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bukti -->
                            @if(count($bukti_files) > 0)
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h3 class="font-semibold text-gray-900 mb-3">Bukti Pendukung</h3>
                                <p class="text-sm text-gray-600">{{ count($bukti_files) }} file dilampirkan</p>
                            </div>
                            @endif
                            
                            <!-- Agreement -->
                            <div class="border border-gray-200 rounded-xl p-4">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model="agreed" class="w-5 h-5 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="font-medium text-gray-900">Pernyataan Kebenaran</div>
                                        <div class="text-sm text-gray-500">Saya menyatakan bahwa informasi yang saya sampaikan adalah benar dan dapat dipertanggungjawabkan. Saya bersedia menerima konsekuensi hukum apabila informasi yang saya berikan terbukti tidak benar.</div>
                                    </div>
                                </label>
                                @error('agreed') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                    
                    <!-- Navigation Buttons -->
                    <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-between">
                        @if($step > 1)
                            <button type="button" wire:click="prevStep"
                                    class="inline-flex items-center gap-2 px-6 py-3 border border-gray-300 rounded-xl font-semibold text-gray-700 hover:bg-gray-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Kembali
                            </button>
                        @else
                            <div></div>
                        @endif
                        
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75">
                            <span wire:loading.remove wire:target="{{ $step === $totalSteps ? 'submit' : 'nextStep' }}">
                                @if($step === $totalSteps)
                                    Kirim Laporan
                                @else
                                    Lanjut
                                @endif
                            </span>
                            <span wire:loading wire:target="{{ $step === $totalSteps ? 'submit' : 'nextStep' }}">
                                Memproses...
                            </span>
                            <svg class="w-5 h-5" wire:loading.remove fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </main>
</div>
