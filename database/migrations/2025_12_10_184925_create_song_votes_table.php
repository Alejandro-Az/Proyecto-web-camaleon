<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_votes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->foreignId('song_id')
                ->constrained('event_songs')
                ->cascadeOnDelete();

            $table->foreignId('guest_id')
                ->nullable()
                ->constrained('guests')
                ->nullOnDelete();

            $table->string('fingerprint', 100)->nullable();

            $table->timestamps();

            $table->index(['event_id', 'song_id']);
            $table->index(['event_id', 'guest_id']);

            $table->unique(['song_id', 'guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_votes');
    }
};
