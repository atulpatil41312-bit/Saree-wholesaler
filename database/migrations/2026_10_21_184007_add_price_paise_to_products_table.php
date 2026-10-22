<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('price_paise')->nullable()->after('wholesale_price');
            $table->index(['shop_id', 'price_paise']);
        });

        DB::table('products')
            ->whereNotNull('wholesale_price')
            ->update(['price_paise' => DB::raw('CAST(ROUND(wholesale_price * 100) AS INTEGER)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'price_paise']);
            $table->dropColumn('price_paise');
        });
    }
};
