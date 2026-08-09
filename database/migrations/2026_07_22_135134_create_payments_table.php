<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_type', ['FULL', 'DOWN_PAYMENT', 'SETTLEMENT']);
            $table->decimal('amount', 12, 2);
            $table->string('payment_gateway')->default('MIDTRANS');
            $table->string('snap_token')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('PENDING');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};