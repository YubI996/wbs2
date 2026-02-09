<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add UUID to aduans table
        Schema::table('aduans', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Generate UUIDs for existing records
        DB::table('aduans')->whereNull('uuid')->orderBy('id')->each(function ($aduan) {
            DB::table('aduans')
                ->where('id', $aduan->id)
                ->update(['uuid' => Str::uuid()->toString()]);
        });

        // Make UUID required and unique
        Schema::table('aduans', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        // Add UUID to bukti_pendukungs table
        Schema::table('bukti_pendukungs', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Generate UUIDs for existing records
        DB::table('bukti_pendukungs')->whereNull('uuid')->orderBy('id')->each(function ($bukti) {
            DB::table('bukti_pendukungs')
                ->where('id', $bukti->id)
                ->update(['uuid' => Str::uuid()->toString()]);
        });

        // Make UUID required and unique
        Schema::table('bukti_pendukungs', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aduans', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('bukti_pendukungs', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
