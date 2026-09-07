<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // reservation_id harus nullable agar admin bisa membuat blok
        // MAINTENANCE manual tanpa reservasi terkait.
        Schema::table('hall_availabilities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservation_id');
        });

        Schema::table('hall_availabilities', function (Blueprint $table) {
            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hall_availabilities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservation_id');
        });

        Schema::table('hall_availabilities', function (Blueprint $table) {
            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
