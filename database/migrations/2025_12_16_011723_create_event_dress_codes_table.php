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
        Schema::create('event_dress_codes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('event_id');

            $table->string('title', 120);

            $table->string('description', 255)->nullable();

            $table->text('examples')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('example_photo_id')->nullable();

            $table->string('icon', 50)->nullable();

            $table->unsignedInteger('display_order')->default(1);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');

            $table->foreign('example_photo_id')
                ->references('id')
                ->on('event_photos')
                ->nullOnDelete();

            $table->index(['event_id', 'is_enabled']);
            $table->index(['event_id', 'display_order']);
            $table->index(['example_photo_id']);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_dress_codes');
    }
};
