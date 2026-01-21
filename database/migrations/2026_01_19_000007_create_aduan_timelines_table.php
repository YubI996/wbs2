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
        Schema::create('aduan_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aduan_id')->constrained('aduans')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User who made the change
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('komentar')->nullable(); // Komentar/catatan pengelola
            $table->boolean('is_public')->default(false); // Ditampilkan ke pelapor atau tidak
            $table->timestamps();
            
            // Index
            $table->index('aduan_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduan_timelines');
    }
};
