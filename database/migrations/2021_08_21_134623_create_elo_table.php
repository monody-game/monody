<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEloTable extends Migration
{
    public function up(): void
    {
        Schema::create('elo', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->smallInteger('elo')->default(2000);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elo');
    }
}
