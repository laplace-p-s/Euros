<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transit_destinations', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('amount'); // 表示順
        });

        // 既存データは登録順（id順）を初期の並び順とする
        DB::statement('UPDATE transit_destinations SET sort_order = id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transit_destinations', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
