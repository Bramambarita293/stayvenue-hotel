<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Penanda idempoten: stok hanya boleh di-release SEKALI seumur reservasi,
            // melindungi dari double-release (webhook retry + admin cancel + cron + polling).
            $table->timestamp('stock_released_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('stock_released_at');
        });
    }
};
