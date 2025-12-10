<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('type', 50);

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('status', 20)->default('draft');

            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('theme_key', 50)->default('default');
            $table->string('primary_color', 20)->nullable();
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->string('font_family', 100)->nullable();

            $table->json('modules')->nullable();

            $table->json('settings')->nullable();

            $table->string('owner_name')->nullable();
            $table->string('owner_email')->nullable();

            $table->unsignedInteger('auto_cleanup_after_days')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('event_date');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
