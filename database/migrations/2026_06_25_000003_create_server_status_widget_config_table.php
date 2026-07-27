<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Global key/value configuration for the Server Status Widget addon.
 * Currently holds the "client_management_enabled" flag that controls whether
 * server owners may manage their own server's widget from the client area.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_status_widget_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_status_widget_config');
    }
};

