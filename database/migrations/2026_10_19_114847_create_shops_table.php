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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('pending')->index();
            $table->string('market_category')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('shop_number')->nullable();
            $table->string('opening_hours')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('whatsapp', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->unsignedTinyInteger('category_limit')->default(5);
            $table->unsignedSmallInteger('product_limit')->default(20);
            $table->unsignedTinyInteger('product_image_limit')->default(2);
            $table->timestamps();
            $table->index(['status', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
