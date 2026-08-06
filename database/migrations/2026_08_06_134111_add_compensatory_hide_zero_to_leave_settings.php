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
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->boolean('compensatory_hide_zero')->default(true)->after('show_compensatory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->dropColumn('compensatory_hide_zero');
        });
    }
};
