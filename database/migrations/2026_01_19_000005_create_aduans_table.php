<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aduans', function (Blueprint $table) {
            $table->id();
            
            // Tracking
            $table->string('nomor_registrasi')->unique(); // WBS-YYYY-NNNNN
            $table->string('tracking_password'); // Hashed password untuk tracking
            $table->unsignedInteger('sequence'); // Sequence number per year
            
            // Pelapor (one of these will be filled)
            $table->foreignId('pelapor_id')->nullable()->constrained('pelapors')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Kategori
            $table->string('jenis_aduan_id');
            $table->foreign('jenis_aduan_id')->references('slug')->on('jenis_aduans')->onDelete('restrict');
            
            // Identitas Terlapor
            $table->text('identitas_terlapor'); // Nama/jabatan pihak yang dilaporkan
            
            // Kronologis 5W+1H
            $table->text('what')->nullable(); // Apa yang terjadi
            $table->text('who')->nullable(); // Siapa yang terlibat
            $table->date('when_date')->nullable(); // Kapan kejadian
            $table->text('where_location')->nullable(); // Di mana kejadian
            $table->text('why')->nullable(); // Mengapa terjadi
            $table->text('how')->nullable(); // Bagaimana kronologinya
            
            // Lokasi tambahan
            $table->string('lokasi_kejadian')->nullable();
            $table->string('koordinat')->nullable(); // lat,lng
            
            // Status & Channel
            $table->enum('status', [
                'pending',      // Baru masuk
                'verifikasi',   // Sedang diverifikasi
                'proses',       // Dalam proses penanganan
                'investigasi',  // Dalam investigasi
                'selesai',      // Selesai ditangani
                'ditolak'       // Ditolak
            ])->default('pending');
            
            $table->enum('channel', [
                'website',
                'whatsapp',
                'instagram',
                'sp4n',
                'superapps'
            ])->default('website');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('channel');
            $table->index('jenis_aduan_id');
            $table->index('created_at');
            $table->index(['sequence', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduans');
    }
};
