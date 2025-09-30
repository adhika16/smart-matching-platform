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
            $table->dropColumn([
                'compensation_min',
                'compensation_max',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->decimal('compensation_min', 10, 2)->nullable()->after('compensation_type');
            $table->decimal('compensation_max', 10, 2)->nullable()->after('compensation_min');
        });
    }
};
