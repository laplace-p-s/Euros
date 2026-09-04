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
        Schema::create('transit_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('use_date');
            $table->string('label', 50); // 行き先（登録時点の名称を保持）
            $table->string('route', 100); // 経路（登録時点の内容を保持）
            $table->unsignedInteger('amount'); // 金額（円）
            $table->string('note', 100)->nullable();
            $table->unsignedBigInteger('destination_id')->nullable(); // 元になったテンプレート
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('transit_destinations')->onDelete('set null');
            $table->index(['user_id', 'use_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transit_records');
    }
};
