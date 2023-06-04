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
        Schema::create('game_outcome_user', function (Blueprint $table) {
            $table->uuid('user_id');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->bigInteger('game_outcome_id', unsigned: true);
            $table
                ->foreign('game_outcome_id')
                ->references('id')
                ->on('game_outcomes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->tinyInteger('role');

            $table->boolean('win');

            $table->tinyInteger('death_round')->nullable();
            $table->string('death_context')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_game_outcome');
    }
};
