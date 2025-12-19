<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AssignDemoEventOwnersSeeder extends Seeder
{
    public function run(): void
    {
        // Si aún no existe la columna (si no ha metido esa migración), no hacemos nada.
        if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'owner_user_id')) {
            return;
        }

        $client = User::query()->where('email', 'client.demo@camaleon.test')->first();

        if (!$client) {
            return;
        }

        // Asignar owner a todos los eventos que no lo tengan (solo en demo/dev).
        Event::query()
            ->whereNull('owner_user_id')
            ->update(['owner_user_id' => $client->id]);
    }
}
