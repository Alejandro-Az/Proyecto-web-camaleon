<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_songs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('url')->nullable();

            $table->text('message_for_couple')->nullable();

            $table->foreignId('suggested_by_guest_id')
                ->nullable()
                ->constrained('guests')
                ->nullOnDelete();

            $table->boolean('show_author')->default(true);

            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('votes_count')->default(0);

            $table->timestamps();

            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_songs');
    }
};
