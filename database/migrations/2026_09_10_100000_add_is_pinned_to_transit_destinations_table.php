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
        Schema::table('transit_destinations', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('sort_order'); // クイック登録へのピン留め
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transit_destinations', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });
    }
};
