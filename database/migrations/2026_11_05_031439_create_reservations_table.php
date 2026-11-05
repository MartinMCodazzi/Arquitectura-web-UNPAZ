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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('status', 20)->default('reservada');
            $table->string('payment_method', 30);
            $table->string('payment_label', 60);
            $table->string('shipping_method', 20);
            $table->string('shipping_summary', 255);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('taxes');
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('customer_name', 100);
            $table->string('customer_email');
            $table->string('billing_invoice_type', 5)->nullable();
            $table->string('billing_tax_condition', 40)->nullable();
            $table->string('billing_tax_id', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
