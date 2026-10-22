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
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropUnique(['slug']);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->string('type')->default('wholesaler')->after('user_id');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->timestamp('suspended_at')->nullable()->after('approved_at');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
            $table->unsignedBigInteger('storage_quota_bytes')->default(104857600)->after('product_image_limit');
            $table->unsignedBigInteger('storage_used_bytes')->default(0)->after('storage_quota_bytes');
            $table->index(['user_id', 'type']);
            $table->unique(['type', 'slug']);
            $table->index(['type', 'status']);
        });

        DB::table('shops')
            ->where('status', 'active')
            ->whereNull('approved_at')
            ->update(['approved_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type']);
            $table->dropUnique(['type', 'slug']);
            $table->dropIndex(['type', 'status']);
            $table->unique('user_id');
            $table->unique('slug');
            $table->dropColumn([
                'type',
                'approved_at',
                'suspended_at',
                'suspension_reason',
                'storage_quota_bytes',
                'storage_used_bytes',
            ]);
        });
    }
};
