<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Cegah dua baris payment menunjuk order Midtrans yang sama.
            // Nullable: baris INITIATED (belum punya order) boleh banyak.
            $table->unique('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['transaction_id']);
        });
    }
};
