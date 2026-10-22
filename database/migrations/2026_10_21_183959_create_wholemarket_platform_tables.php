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
        Schema::create('shop_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('owner');
            $table->json('capabilities')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('template_key')->default('classic');
            $table->string('colour_key')->nullable();
            $table->string('font_key')->nullable();
            $table->json('sections')->nullable();
            $table->boolean('show_prices')->default(true);
            $table->timestamps();
        });

        Schema::create('shop_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('shop_type');
            $table->string('old_slug');
            $table->string('redirect_to')->nullable();
            $table->timestamps();
            $table->unique(['shop_type', 'old_slug']);
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('shop_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('shop_type')->default('wholesaler');
            $table->boolean('signup_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->unsignedBigInteger('price_paise')->nullable();
            $table->string('billing_interval')->default('monthly');
            $table->json('limits');
            $table->json('template_keys')->nullable();
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_to')->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'version']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('plan_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('source')->default('manual');
            $table->string('billing_interval')->default('monthly');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paid_through_at')->nullable();
            $table->timestamp('grace_until')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('overrides')->nullable();
            $table->timestamps();
            $table->index(['shop_id', 'status', 'paid_through_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_paise');
            $table->string('status')->default('recorded');
            $table->string('method')->default('manual');
            $table->string('reference')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['method', 'reference']);
            $table->index(['shop_id', 'paid_at']);
        });

        Schema::create('billing_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_event_id');
            $table->string('event_type');
            $table->string('status')->default('received');
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_event_id']);
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('retailer_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->string('email');
            $table->string('token_hash', 64)->unique();
            $table->unsignedTinyInteger('term_days');
            $table->string('status')->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['sponsor_shop_id', 'status', 'expires_at']);
        });

        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('retailer_shop_id')->unique()->constrained('shops')->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('grace_until')->nullable();
            $table->timestamp('independently_paid_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['sponsor_shop_id', 'status', 'expires_at']);
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->text('message');
            $table->string('status')->default('new');
            $table->timestamps();
            $table->index(['shop_id', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['shop_id', 'action']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('mail');
            $table->string('notification_type');
            $table->string('dedupe_key')->unique();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        DB::table('shops')->orderBy('id')->each(function (object $shop): void {
            DB::table('shop_memberships')->insert([
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id,
                'role' => 'owner',
                'capabilities' => json_encode(['manage_catalogue', 'manage_branding', 'manage_billing']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('shop_settings')->insert([
                'shop_id' => $shop->id,
                'template_key' => $shop->template ?? 'classic',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('sponsorships');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('billing_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plan_versions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('shop_redirects');
        Schema::dropIfExists('shop_settings');
        Schema::dropIfExists('shop_memberships');
    }
};
