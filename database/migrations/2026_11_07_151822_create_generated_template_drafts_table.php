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
        Schema::create('generated_template_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stitch_project_id');
            $table->string('screen_id')->nullable();
            $table->string('title');
            $table->text('prompt');
            $table->string('device_type')->default('DESKTOP');
            $table->string('status')->default('pending');
            $table->string('html_path')->nullable();
            $table->string('image_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_template_drafts');
    }
};
