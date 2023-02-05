<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->primary('user_id');
            $table->uuid('user_id')->unique();
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->smallInteger('win_streak')->default(0);
            $table->smallInteger('longest_streak')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
