<?php

namespace App\Livewire;

use App\Enums\AduanStatus;
use App\Enums\ReportChannel;
use App\Models\Aduan;
use App\Models\JenisAduan;
use App\Models\Pelapor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class BuatLaporanWizard extends Component
{
    use WithFileUploads;
    
    public int $step = 1;
    public int $totalSteps = 5;
    
    // Step 1: Identitas Pelapor
    public string $nama = '';
    public string $phone = '';
    public string $email = '';
    public bool $is_anonim = false;
    public bool $notify_email = false;
    
    // Step 2: Substansi Laporan
    public string $jenis_aduan_id = '';
    public string $identitas_terlapor = '';
    
    // Step 3: Kronologis 5W+1H
    public string $what = '';
    public string $who = '';
    public ?string $when_date = null;
    public string $where_location = '';
    public string $why = '';
    public string $how = '';
    public string $lokasi_kejadian = '';
    
    // Step 4: Bukti
    public array $bukti_files = [];
    
    // Step 5: Success
    public bool $submitted = false;
    public string $nomor_registrasi = '';
    public string $tracking_password = '';
    public bool $agreed = false;
    
    // Cached data
    public array $jenisAduanOptions = [];
    
    public function mount(): void
    {
        $this->loadCachedData();
    }
    
    protected function loadCachedData(): void
    {
        // Cache jenis aduan options for 1 hour
        $this->jenisAduanOptions = Cache::remember('jenis_aduans_active', 3600, function () {
            return JenisAduan::active()
                ->orderBy('slug')
                ->pluck('name', 'slug')
                ->toArray();
        });
    }
    
    public function rules(): array
    {
        return match($this->step) {
            1 => [
                'nama' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => $this->notify_email ? 'required|email|max:255' : 'nullable|email|max:255',
                'is_anonim' => 'boolean',
                'notify_email' => 'boolean',
            ],
            2 => [
                'jenis_aduan_id' => ['required', Rule::in(array_keys($this->jenisAduanOptions))],
                'identitas_terlapor' => 'required|string|max:1000',
            ],
            3 => [
                'what' => 'required|string|max:5000',
                'who' => 'nullable|string|max:1000',
                'when_date' => 'nullable|date',
                'where_location' => 'nullable|string|max:1000',
                'why' => 'nullable|string|max:2000',
                'how' => 'nullable|string|max:5000',
                'lokasi_kejadian' => 'nullable|string|max:255',
            ],
            4 => [
                'bukti_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,webp',
            ],
            5 => [
                'agreed' => 'accepted',
            ],
            default => [],
        };
    }
    
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi',
            'phone.required' => 'Nomor handphone wajib diisi',
            'email.required' => 'Email wajib diisi jika ingin menerima notifikasi',
            'email.email' => 'Format email tidak valid',
            'jenis_aduan_id.required' => 'Kategori laporan wajib dipilih',
            'identitas_terlapor.required' => 'Identitas pihak terlapor wajib diisi',
            'what.required' => 'Uraian kejadian (Apa yang terjadi) wajib diisi',
            'agreed.accepted' => 'Anda harus menyetujui pernyataan kebenaran informasi',
            'bukti_files.*.max' => 'Ukuran file maksimal 10MB',
            'bukti_files.*.mimes' => 'Format file harus PDF, DOC, DOCX, JPG, PNG, atau WEBP',
        ];
    }
    
    public function nextStep(): void
    {
        $this->validate();
        
        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }
    
    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }
    
    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->step) {
            $this->step = $step;
        }
    }
    
    public function submit(): void
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Create Pelapor
            $pelapor = new Pelapor();
            $pelapor->nama = $this->nama;
            $pelapor->phone = $this->phone;
            $pelapor->email = $this->email ?: null;
            $pelapor->notify_email = $this->notify_email;
            $pelapor->is_anonim = $this->is_anonim;
            
            // Encrypt identity if anonymous
            if ($this->is_anonim) {
                $pelapor->encryptAndStoreIdentity($this->nama, $this->phone);
            }
            
            $pelapor->save();
            
            // Create Aduan
            $aduan = new Aduan();
            $aduan->pelapor_id = $pelapor->id;
            $aduan->jenis_aduan_id = $this->jenis_aduan_id;
            $aduan->identitas_terlapor = $this->identitas_terlapor;
            $aduan->what = $this->what;
            $aduan->who = $this->who ?: null;
            $aduan->when_date = $this->when_date ?: null;
            $aduan->where_location = $this->where_location ?: null;
            $aduan->why = $this->why ?: null;
            $aduan->how = $this->how ?: null;
            $aduan->lokasi_kejadian = $this->lokasi_kejadian ?: null;
            $aduan->status = AduanStatus::PENDING;
            $aduan->channel = ReportChannel::WEBSITE;
            
            // Generate registration number and password
            $aduan->generateNomorRegistrasi();
            $plainPassword = $aduan->generateTrackingPassword();
            
            $aduan->save();
            
            // Create initial timeline
            $aduan->timelines()->create([
                'new_status' => AduanStatus::PENDING->value,
                'komentar' => 'Laporan berhasil dikirim',
                'is_public' => true,
            ]);
            
            // Handle file uploads
            foreach ($this->bukti_files as $file) {
                $path = $file->store('bukti-pendukung', 'public');
                
                $aduan->buktiPendukungs()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $this->getFileType($file->getMimeType()),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
            
            DB::commit();
            
            // Store for display
            $this->nomor_registrasi = $aduan->nomor_registrasi;
            $this->tracking_password = $plainPassword;
            $this->submitted = true;
            
            // Dispatch email notification if opted in
            if ($this->notify_email && $this->email) {
                \App\Jobs\SendReportSubmittedEmail::dispatch($aduan, $plainPassword, $this->email);
            }
            
            // Clear stats cache
            Cache::forget('landing_stats');
            Cache::forget('admin_stats');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    protected function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'foto';
        }
        
        if (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'dokumen';
        }
        
        return 'lainnya';
    }
    
    public function removeFile(int $index): void
    {
        unset($this->bukti_files[$index]);
        $this->bukti_files = array_values($this->bukti_files);
    }
    
    public function render()
    {
        return view('livewire.buat-laporan-wizard')
            ->layout('components.layouts.guest', ['title' => 'Buat Laporan - WBS Kota Bontang']);
    }
}
