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
        Schema::create('server_status_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('server_id');
            $table->timestamp('pinged_at');
            $table->boolean('online');
            $table->integer('player_count')->nullable();
            $table->integer('ping_ms')->nullable();

            $table->index('server_id');
            $table->index('pinged_at');
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_status_history');
    }
};

