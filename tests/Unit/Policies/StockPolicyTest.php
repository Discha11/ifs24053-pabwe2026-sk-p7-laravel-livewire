<?php

namespace Tests\Unit\Policies;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_is_allowed_and_others_denied(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $stock = Stock::factory()->for($owner)->create();
        $policy = new StockPolicy;

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue($policy->{$ability}($owner, $stock));
            $this->assertFalse($policy->{$ability}($other, $stock));
        }
    }

    public function test_policy_is_registered(): void
    {
        $stock = Stock::factory()->create();

        $this->assertTrue($stock->user->can('update', $stock));
        $this->assertFalse(User::factory()->create()->can('update', $stock));
    }
}
