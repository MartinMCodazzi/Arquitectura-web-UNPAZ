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
        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40)->default('Cuenta');
            $table->string('type', 20)->default('banco');
            $table->string('bank_name', 80)->nullable();
            $table->string('holder_name', 100);
            $table->string('holder_dni', 10);
            $table->string('cbu_alias', 60);
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
        Schema::dropIfExists('customer_accounts');
    }
};
