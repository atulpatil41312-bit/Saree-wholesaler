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
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            // Lookup tables are created immediately after products. The foreign keys are
            // attached by the follow-up migration once both tables exist on PostgreSQL.
            $table->unsignedBigInteger('saree_type_id')->nullable();
            $table->unsignedBigInteger('fabric_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('code');
            $table->string('colour')->nullable();
            $table->text('description')->nullable();
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->unsignedInteger('minimum_order_quantity')->default(1);
            $table->unsignedInteger('pack_size')->nullable();
            $table->string('blouse_piece')->default('not_specified');
            $table->string('availability')->default('available');
            $table->timestamps();
            $table->unique(['shop_id', 'code']);
            $table->unique(['shop_id', 'slug']);
            $table->index(['shop_id', 'category_id', 'wholesale_price']);
            $table->index(['shop_id', 'fabric_id', 'availability']);
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
