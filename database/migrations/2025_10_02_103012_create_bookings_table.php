<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->enum('status', ['pending','confirmed','cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['user_id','ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
