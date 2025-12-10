<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_locations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('type', 30);

            $table->string('name');
            $table->text('address')->nullable();
            $table->string('maps_url')->nullable();

            $table->unsignedTinyInteger('display_order')->default(1);

            $table->timestamps();

            $table->index(['event_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_locations');
    }
};
