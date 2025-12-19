<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_stories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('event_id');

            $table->string('title', 120);
            $table->string('subtitle', 255)->nullable();

            // Texto principal (larga extensión)
            $table->longText('body')->nullable();

            // Imagen opcional (reutiliza event_photos)
            $table->unsignedBigInteger('example_photo_id')->nullable();

            $table->unsignedInteger('display_order')->default(1);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');

            $table->foreign('example_photo_id')
                ->references('id')->on('event_photos')
                ->nullOnDelete();

            $table->index(['event_id', 'is_enabled']);
            $table->index(['event_id', 'display_order']);
            $table->index(['example_photo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_stories');
    }
};
