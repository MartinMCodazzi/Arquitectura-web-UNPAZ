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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Precio en CÉNTIMOS de pesos argentinos (ARS): entero, nunca float.
            $table->unsignedBigInteger('price');

            $table->unsignedInteger('stock')->default(0);

            // Ruta relativa dentro de storage/app/public (p. ej. products/abc.webp).
            $table->string('image_path')->nullable();

            // No se puede borrar una categoría que todavía tiene productos.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices compuestos que siguen los patrones reales del catálogo:
            // listado por categoría, filtro/orden por precio y orden por fecha.
            $table->index(['is_active', 'category_id'], 'products_active_category_index');
            $table->index(['is_active', 'price'], 'products_active_price_index');
            $table->index(['is_active', 'created_at'], 'products_active_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
