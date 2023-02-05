<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_outcome', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_id');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->tinyInteger('role_id');

            $table->boolean('win');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_outcome');
    }
};
