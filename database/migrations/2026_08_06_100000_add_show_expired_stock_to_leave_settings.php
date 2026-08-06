<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->boolean('show_expired_stock')->default(false)->after('annual_leave_grant_days');
        });
    }

    public function down(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->dropColumn('show_expired_stock');
        });
    }
};
