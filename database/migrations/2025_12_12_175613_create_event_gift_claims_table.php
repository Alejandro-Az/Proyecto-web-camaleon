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
        Schema::create('event_gift_claims', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->foreignId('gift_id')
                ->constrained('event_gifts')
                ->cascadeOnDelete();

            $table->foreignId('guest_id')
                ->constrained('guests')
                ->cascadeOnDelete();

            // Unidades que este invitado se compromete a comprar
            $table->unsignedInteger('quantity')->default(1);

            // reserved | purchased | cancelled
            $table->string('status', 20)->default('reserved');

            $table->timestamps();

            $table->index('event_id');
            $table->index('gift_id');
            $table->index('guest_id');

            // Un claim activo por invitado y regalo (cantidad se guarda en la columna)
            $table->unique(['event_id', 'gift_id', 'guest_id'], 'event_gift_claims_unique_guest_per_gift');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_gift_claims');
    }
};
