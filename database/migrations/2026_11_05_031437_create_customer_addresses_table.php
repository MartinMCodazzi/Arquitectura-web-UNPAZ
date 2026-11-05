<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40)->default('Domicilio');
            $table->string('recipient_name', 100);
            $table->string('phone', 30);
            $table->string('street', 120);
            $table->string('number', 20);
            $table->string('floor', 20)->nullable();
            $table->string('city', 80);
            $table->string('province', 80);
            $table->string('postal_code', 10);
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
