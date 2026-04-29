<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DummyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
