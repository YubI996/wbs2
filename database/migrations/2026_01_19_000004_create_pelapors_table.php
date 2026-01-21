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
        Schema::create('pelapors', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama pelapor
            $table->string('phone', 20); // No HP (wajib)
            $table->string('email')->nullable(); // Email (opsional)
            $table->boolean('is_anonim')->default(false); // Opsi anonim
            $table->text('encrypted_identity')->nullable(); // Data terenkripsi jika anonim
            $table->boolean('notify_email')->default(false); // Terima notifikasi email
            $table->timestamps();
            
            // Index
            $table->index('phone');
            $table->index('email');
            $table->index('is_anonim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelapors');
    }
};
