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
        Schema::create('server_status_widget_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('server_id');
            $table->boolean('enabled')->default(false);
            $table->string('theme', 20)->default('dark');
            $table->text('allowed_domains')->nullable();
            $table->timestamps();

            $table->unique('server_id');
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_status_widget_settings');
    }
};

