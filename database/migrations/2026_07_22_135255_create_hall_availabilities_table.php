<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hall_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('hall_sessions');
            $table->date('event_date');
            $table->string('status')->default('LOCKED');
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['hall_id', 'session_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_availabilities');
    }
};