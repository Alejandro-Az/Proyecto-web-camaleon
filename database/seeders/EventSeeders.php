<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EventSeeders extends Seeder
{
    /**
     * Seeders relacionados con eventos.
     */
    public function run(): void
    {
        $this->call([
            DemoEventsSeeder::class,
        ]);
    }
}
