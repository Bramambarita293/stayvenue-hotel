<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_rooms')->default(1);
            $table->string('assigned_room_number')->nullable();
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};