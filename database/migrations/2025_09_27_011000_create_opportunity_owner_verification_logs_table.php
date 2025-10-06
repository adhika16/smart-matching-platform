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
        Schema::create('opportunity_owner_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_owner_profile_id');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role');
            $table->string('action');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('opportunity_owner_profile_id', 'verification_logs_owner_profile_fk')->references('id')->on('opportunity_owner_profiles')->onDelete('cascade');
            $table->index(['opportunity_owner_profile_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_owner_verification_logs');
    }
};
