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
        Schema::table('job_postings', function (Blueprint $table) {
            $table->string('category')->nullable()->after('tags');
            $table->json('skills')->nullable()->after('category');
            $table->date('timeline_start')->nullable()->after('published_at');
            $table->date('timeline_end')->nullable()->after('timeline_start');
            $table->decimal('budget_min', 12, 2)->nullable()->after('timeline_end');
            $table->decimal('budget_max', 12, 2)->nullable()->after('budget_min');

            $table->index(['category', 'status']);
            $table->index('timeline_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropIndex(['category', 'status']);
            $table->dropIndex(['timeline_start']);

            $table->dropColumn([
                'category',
                'skills',
                'timeline_start',
                'timeline_end',
                'budget_min',
                'budget_max',
            ]);
        });
    }
};
