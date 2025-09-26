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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['creative', 'opportunity_owner'])->after('email');
            $table->timestamp('profile_completed_at')->nullable()->after('user_type');
            $table->integer('profile_completion_score')->default(0)->after('profile_completed_at');
        });

        // Create creative_profiles table
        Schema::create('creative_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->json('portfolio_links')->nullable();
            $table->string('location')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->enum('experience_level', ['beginner', 'intermediate', 'expert'])->nullable();
            $table->boolean('available_for_work')->default(true);
            $table->timestamps();
        });

        // Create opportunity_owner_profiles table
        Schema::create('opportunity_owner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->text('company_description')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_size')->nullable();
            $table->string('industry')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creative_profiles');
        Schema::dropIfExists('opportunity_owner_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'profile_completed_at', 'profile_completion_score']);
        });
    }
};
