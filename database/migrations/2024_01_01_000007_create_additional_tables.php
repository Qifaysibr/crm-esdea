<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('target_type'); // individual, team, store
            $table->string('period_type')->default('monthly'); // daily, weekly, monthly, yearly
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->integer('week')->nullable();
            
            $table->decimal('target_amount', 15, 2);
            $table->decimal('achieved_amount', 15, 2)->default(0);
            $table->decimal('achievement_percentage', 5, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['user_id', 'year', 'month']);
        });

        Schema::create('marketing_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // pdf, image, video, etc
            $table->integer('file_size'); // in bytes
            $table->string('category')->nullable(); // brochure, presentation, profile, etc
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->integer('download_count')->default(0);
            $table->timestamps();
            
            $table->index('category');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // create, update, delete, login, etc
            $table->string('model_type')->nullable(); // Model class name
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
        Schema::dropIfExists('marketing_assets');
        Schema::dropIfExists('audit_logs');
    }
};
