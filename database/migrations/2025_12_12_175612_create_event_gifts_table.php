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
        Schema::create('event_gifts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('name');

            $table->text('description')->nullable();

            $table->string('store_label', 100)->nullable();

            $table->string('url', 500)->nullable();

            // Cantidad total deseada y cantidad ya reservada
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('quantity_reserved')->default(0);

            // pending | reserved | purchased
            $table->string('status', 20)->default('pending');

            // Invitado "representativo" usado para mostrar "Comprado por X" (opcional)
            $table->foreignId('claimed_by_guest_id')
                ->nullable()
                ->constrained('guests')
                ->nullOnDelete();

            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('purchased_at')->nullable();

            $table->unsignedSmallInteger('display_order')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('event_id');
            $table->index(['event_id', 'display_order']);
            $table->index('status');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_gifts');
    }
};
