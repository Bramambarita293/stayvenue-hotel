<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            // Memberikan nilai default 0 agar MySQL tidak melempar error saat insert data baru
            $table->integer('total_inventory')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->integer('total_inventory')->change();
        });
    }
};