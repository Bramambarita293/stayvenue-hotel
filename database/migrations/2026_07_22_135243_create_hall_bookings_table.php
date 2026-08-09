<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hall_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('hall_id')->constrained();
            $table->foreignId('event_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('session_id')->constrained('hall_sessions');
            $table->date('event_date');
            $table->string('event_type');
            $table->timestamp('vendor_setup_time')->nullable();
            $table->text('special_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_bookings');
    }
};