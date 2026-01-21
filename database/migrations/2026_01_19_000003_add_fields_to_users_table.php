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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 18)->nullable()->after('email'); // NIP untuk ASN
            $table->string('nik', 16)->nullable()->after('nip'); // NIK untuk masyarakat
            $table->string('phone', 20)->nullable()->after('nik');
            $table->string('role_id')->nullable()->after('phone');
            
            // Foreign key to roles
            $table->foreign('role_id')
                  ->references('slug')
                  ->on('roles')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('nip');
            $table->index('nik');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropIndex(['nip']);
            $table->dropIndex(['nik']);
            $table->dropIndex(['role_id']);
            $table->dropColumn(['nip', 'nik', 'phone', 'role_id']);
        });
    }
};
