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
        Schema::create('leave_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('leave_type', 20); // paid, annual, compensatory
            $table->date('usage_date');
            $table->decimal('days', 3, 1); // 0.5 or 1.0
            $table->string('note', 100)->nullable();
            $table->unsignedBigInteger('record_id')->nullable(); // 将来の勤怠連携用
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('set null');
            $table->index(['user_id', 'leave_type', 'usage_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_usages');
    }
};
