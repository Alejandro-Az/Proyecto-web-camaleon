<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();

            $table->string('invitation_code', 50);

            $table->unsignedInteger('invited_seats')->default(1);

            $table->string('rsvp_status', 20)->default('pending');

            $table->text('rsvp_message')->nullable();
            $table->boolean('rsvp_public')->default(false);

            $table->unsignedInteger('guests_confirmed')->nullable();

            $table->boolean('show_in_public_list')->default(false);

            $table->timestamp('checked_in_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'invitation_code']);

            $table->index(['event_id', 'rsvp_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
