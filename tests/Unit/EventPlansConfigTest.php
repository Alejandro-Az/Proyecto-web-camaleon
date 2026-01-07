<?php

namespace Tests\Unit;

use Tests\TestCase;

class EventPlansConfigTest extends TestCase
{
    /** @test */
    public function event_plans_config_no_debe_estar_rota(): void
    {
        $default = config('event_plans.default_plan');
        $plans   = config('event_plans.plans');

        $this->assertIsString($default, 'default_plan debe ser string');
        $this->assertNotEmpty($default, 'default_plan no debe venir vacío');

        $this->assertIsArray($plans, 'plans debe ser array');
        $this->assertArrayHasKey($default, $plans, 'default_plan debe existir dentro de plans');

        foreach ($plans as $key => $plan) {
            $this->assertIsArray($plan, "Plan '{$key}' debe ser array");
            $modules = $plan['modules'] ?? null;

            $this->assertIsArray($modules, "Plan '{$key}' debe tener 'modules' como array");
            $this->assertNotEmpty($modules, "Plan '{$key}' no debe tener 'modules' vacío (si se vacía, isModuleAvailable fail-closed bloqueará todo)");
        }
    }
}
