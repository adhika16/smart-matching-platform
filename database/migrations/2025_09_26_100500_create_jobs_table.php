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
    Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('compensation_type', ['hourly', 'project', 'salary'])->nullable();
            $table->decimal('compensation_min', 10, 2)->nullable();
            $table->decimal('compensation_max', 10, 2)->nullable();
            $table->json('tags')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('job_postings');
    }
};
