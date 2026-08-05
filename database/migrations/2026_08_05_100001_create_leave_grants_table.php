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
        Schema::create('leave_grants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('leave_type', 20); // paid, annual, compensatory
            $table->smallInteger('fiscal_year');
            $table->decimal('grant_days', 4, 1);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable(); // null = 期限なし
            $table->boolean('is_auto')->default(false);
            $table->string('note', 100)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'leave_type', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_grants');
    }
};
