<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Kolom ini mati: accessor RoomType::getTotalInventoryAttribute()
        // selalu menimpa nilai dari DB. Kapasitas kini dihitung live dari
        // relasi rooms (dengan withCount) — lihat RoomTypesTable & welcome view.
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('total_inventory');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->integer('total_inventory')->default(0);
        });
    }
};
