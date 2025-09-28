<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embeddings_cache', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('model_version')->nullable();
            $table->json('vector_data');
            $table->unsignedInteger('dimension');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'model_version'], 'embeddings_entity_model_unique');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embeddings_cache');
    }
};
