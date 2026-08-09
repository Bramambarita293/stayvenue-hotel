<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->enum('reservation_type', ['ROOM', 'HALL']);
            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('PENDING_PAYMENT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};