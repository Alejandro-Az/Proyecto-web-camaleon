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
        Schema::create('event_photos', function (Blueprint $table) {
            $table->id();

            // Evento al que pertenece la foto
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            // Invitado que subió la foto (null en galería oficial del dueño)
            $table->foreignId('guest_id')
                ->nullable()
                ->constrained('guests')
                ->nullOnDelete();

            // Tipo de foto: gallery, hero, guest_upload, etc.
            $table->string('type', 30)->default('gallery');

            // Rutas de los archivos en el disco (storage)
            $table->string('file_path', 2048);
            $table->string('thumbnail_path', 2048)->nullable();

            // Texto descriptivo opcional
            $table->string('caption', 255)->nullable();

            // Estado de moderación: approved, pending, rejected
            $table->string('status', 20)->default('approved');

            // Orden de despliegue en la galería
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Índices útiles
            $table->index(['event_id', 'type']);
            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'type', 'status']);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_photos');
    }
};
