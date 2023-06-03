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

            $table->tinyInteger('role');

            $table->boolean('win');

            /** We store the role instead of team, because it is easier to retrieve team from role, than reverse. It takes count of loners' wins */
            $table->string('winning_role');

            $table->tinyInteger('round');

            /** Game's roles list */
            $table->json('composition');

            $table->uuid('owner_id');
            $table
                ->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');

            $table->json('users');

            $table->timestamp('played_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_outcome');
    }
};
