<?php

namespace Tests\Unit;

use App\Models\Event;
use Tests\TestCase;

class EventModulesTest extends TestCase
{
    /** @test */
    public function usa_defaults_cuando_falta_la_llave()
    {
        config(['event_modules.defaults.map' => false]);

        $event = new Event();
        $event->setAttribute('modules', []); // no trae "map"

        $this->assertFalse($event->isModuleEnabled('map'));
    }

    /** @test */
    public function mapea_llaves_legacy_a_la_llave_canonica()
    {
        config([
            'event_modules.defaults.songs' => false,
            'event_modules.legacy_aliases' => [
                'playlist_suggestions' => 'songs',
            ],
        ]);

        $event = new Event();
        $event->setAttribute('modules', [
            'playlist_suggestions' => true, // legacy
        ]);

        $this->assertTrue($event->isModuleEnabled('songs'));
    }

    /** @test */
    public function la_llave_canonica_gana_sobre_la_legacy()
    {
        config([
            'event_modules.defaults.songs' => true,
            'event_modules.legacy_aliases' => [
                'playlist_suggestions' => 'songs',
            ],
        ]);

        $event = new Event();
        $event->setAttribute('modules', [
            'songs'                => false, // canónica
            'playlist_suggestions' => true,  // legacy
        ]);

        $this->assertFalse($event->isModuleEnabled('songs'));
    }
}
