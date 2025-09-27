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
		Schema::table('opportunity_owner_profiles', function (Blueprint $table) {
			$table->index(['is_verified', 'verified_at']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('opportunity_owner_profiles', function (Blueprint $table) {
			$table->dropIndex('opportunity_owner_profiles_is_verified_verified_at_index');
		});
	}
};
