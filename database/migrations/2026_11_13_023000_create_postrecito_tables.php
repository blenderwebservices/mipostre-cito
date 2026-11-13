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
        Schema::create('flavors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('fa-solid fa-ice-cream');
            $table->string('bg_color')->default('bg-pink-100');
            $table->string('text_color')->default('text-pink-600');
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('capacity');
            $table->decimal('base_price', 10, 2);
            $table->text('description');
            $table->string('icon')->default('fa-solid fa-jar');
            $table->string('badge')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flavor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('presentation_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->integer('stock')->default(50);
            $table->string('image_url')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_promo')->default(false);
            $table->boolean('is_returnable_container')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('pendiente'); // pendiente, confirmado, en_preparacion, en_ruta, entregado, cancelado
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('delivery_zone');
            $table->string('delivery_address');
            $table->date('scheduled_date');
            $table->string('scheduled_slot'); // ej: 10:00 - 13:00, 13:00 - 16:00, 16:00 - 19:00
            $table->string('payment_method')->default('efectivo'); // efectivo, transferencia, tarjeta_entrega
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->string('source')->default('web'); // web, chatbot
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('source_type'); // product, presentation, flavor, service_policy
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('title');
            $table->text('content');
            $table->json('embedding')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('presentations');
        Schema::dropIfExists('flavors');
    }
};

