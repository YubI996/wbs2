<div>
    <!-- Hero Section -->
    <section class="gradient-primary min-h-[600px] relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
                <rect width="100%" height="100%" fill="url(#grid)"/>
            </svg>
        </div>
        
        <div class="container mx-auto px-4 py-16 relative z-10">
            <!-- Header -->
            <header class="flex justify-between items-center mb-16">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-white font-bold text-xl">WBS</h1>
                        <p class="text-blue-200 text-sm">Kota Bontang</p>
                    </div>
                </div>
                
                <nav class="flex items-center gap-4">
                    <a href="{{ route('cek-status') }}" class="text-white hover:text-blue-200 transition font-medium">
                        Cek Status
                    </a>
                    <a href="{{ route('login') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition font-medium backdrop-blur">
                        Login
                    </a>
                </nav>
            </header>
            
            <!-- Hero Content -->
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-6 leading-tight">
                    Whistle Blowing System
                    <span class="block text-blue-200">Kota Bontang</span>
                </h2>
                
                <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
                    Laporkan dugaan pelanggaran secara aman dan rahasia. 
                    Identitas Anda akan dilindungi sesuai ketentuan yang berlaku.
                </p>
                
                <!-- Main CTA -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                    <a href="{{ route('buat-laporan') }}" 
                       class="hover-lift inline-flex items-center justify-center gap-3 bg-white text-blue-600 px-8 py-4 rounded-xl font-bold text-lg shadow-xl hover:bg-blue-50 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Laporan
                    </a>
                    
                    <a href="{{ route('cek-status') }}" 
                       class="hover-lift inline-flex items-center justify-center gap-3 bg-white/20 text-white px-8 py-4 rounded-xl font-bold text-lg backdrop-blur hover:bg-white/30 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cek Status Laporan
                    </a>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="glass-card rounded-xl p-6 text-center">
                        <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['total_laporan'] ?? 0) }}</div>
                        <div class="text-blue-200 text-sm">Total Laporan</div>
                    </div>
                    <div class="glass-card rounded-xl p-6 text-center">
                        <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['laporan_selesai'] ?? 0) }}</div>
                        <div class="text-blue-200 text-sm">Laporan Selesai</div>
                    </div>
                    <div class="glass-card rounded-xl p-6 text-center">
                        <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['dalam_proses'] ?? 0) }}</div>
                        <div class="text-blue-200 text-sm">Dalam Proses</div>
                    </div>
                    <div class="glass-card rounded-xl p-6 text-center">
                        <div class="text-3xl font-bold text-white mb-1">{{ $stats['kategori'] ?? 0 }}</div>
                        <div class="text-blue-200 text-sm">Kategori</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Wave Decoration -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>
    
    <!-- Main Content -->
    <main id="main-content" class="container mx-auto px-4 py-16">
        <!-- Multi-Channel Section -->
        <section class="mb-20">
            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Saluran Pelaporan</h3>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Pilih saluran yang paling nyaman untuk Anda
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <!-- Website Form - Primary -->
                <a href="{{ route('buat-laporan') }}" 
                   class="hover-lift bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 text-center text-white shadow-lg">
                    <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Website</h4>
                    <p class="text-blue-100 text-sm">Form lengkap dengan tracking</p>
                    <span class="inline-block mt-3 bg-white/20 text-sm px-3 py-1 rounded-full">Direkomendasikan</span>
                </a>
                
                <!-- WhatsApp -->
                <a href="https://wa.me/628115859300" target="_blank" rel="noopener"
                   class="hover-lift bg-white rounded-2xl p-6 text-center shadow-lg border border-gray-100">
                    <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg text-gray-900 mb-2">WhatsApp</h4>
                    <p class="text-gray-500 text-sm">0811-5859-300</p>
                </a>
                
                <!-- Instagram -->
                <a href="https://instagram.com/itdabontang" target="_blank" rel="noopener"
                   class="hover-lift bg-white rounded-2xl p-6 text-center shadow-lg border border-gray-100">
                    <div class="w-16 h-16 bg-pink-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg text-gray-900 mb-2">Instagram</h4>
                    <p class="text-gray-500 text-sm">DM ke @itdabontang</p>
                </a>
                
                <!-- SP4N LAPOR -->
                <a href="https://lapor.go.id" target="_blank" rel="noopener"
                   class="hover-lift bg-white rounded-2xl p-6 text-center shadow-lg border border-gray-100">
                    <div class="w-16 h-16 bg-red-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg text-gray-900 mb-2">SP4N LAPOR!</h4>
                    <p class="text-gray-500 text-sm">Kanal pengaduan nasional</p>
                </a>
            </div>
        </section>
        
        <!-- How It Works -->
        <section class="mb-20">
            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Cara Melaporkan</h3>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Proses pelaporan yang mudah dan aman
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-8 max-w-5xl mx-auto">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 font-bold text-xl">1</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Isi Identitas</h4>
                    <p class="text-gray-500 text-sm">Lengkapi data diri Anda. Bisa memilih untuk anonim.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 font-bold text-xl">2</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Isi Laporan</h4>
                    <p class="text-gray-500 text-sm">Jelaskan kronologis kejadian dengan lengkap (5W+1H).</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 font-bold text-xl">3</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Lampirkan Bukti</h4>
                    <p class="text-gray-500 text-sm">Upload dokumen atau foto pendukung laporan.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 font-bold text-xl">4</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Dapatkan Nomor</h4>
                    <p class="text-gray-500 text-sm">Simpan nomor registrasi untuk memantau status.</p>
                </div>
            </div>
        </section>
        
        <!-- CTA Section -->
        <section class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-3xl p-12 text-center text-white">
            <h3 class="text-3xl font-bold mb-4">Siap Melaporkan?</h3>
            <p class="text-blue-100 mb-8 max-w-xl mx-auto">
                Keberanian Anda untuk melaporkan dapat membantu menciptakan pemerintahan yang bersih dan transparan.
            </p>
            <a href="{{ route('buat-laporan') }}" 
               class="inline-flex items-center gap-2 bg-white text-blue-600 px-8 py-4 rounded-xl font-bold text-lg shadow-xl hover:bg-blue-50 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Laporan Sekarang
            </a>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-white font-bold">WBS Kota Bontang</div>
                            <div class="text-sm">Whistle Blowing System</div>
                        </div>
                    </div>
                    <p class="text-sm">
                        Sistem pelaporan pengaduan yang aman, rahasia, dan terpercaya untuk mewujudkan pemerintahan yang bersih.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Link Cepat</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('buat-laporan') }}" class="hover:text-white transition">Buat Laporan</a></li>
                        <li><a href="{{ route('cek-status') }}" class="hover:text-white transition">Cek Status Laporan</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Login</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm">
                        <li>Pemerintah Kota Bontang</li>
                        <li>Jl. Bessai Berinta, Bontang</li>
                        <li>Kalimantan Timur</li>
                        <li class="pt-2">
                            <a href="https://wa.me/628115859300" target="_blank" rel="noopener" class="inline-flex items-center gap-2 hover:text-white transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                0811-5859-300
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} WBS Kota Bontang. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>
</div>
