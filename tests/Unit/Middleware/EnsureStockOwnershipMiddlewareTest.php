<?php

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function request(?User $user, string $uri, ?Stock $bound = null): Request
    {
        $request = Request::create($uri);
        $route = new Route('GET', 's/{stock}', fn () => null);
        $route->bind($request);

        if ($bound) {
            $route->setParameter('stock', $bound);
        }

        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_owner_passes_with_bound_model(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        $response = (new EnsureStockOwnership)->handle(
            $this->request($user, "s/{$stock->id}", $stock),
            fn () => response('ok')
        );

        $this->assertSame('ok', $response->getContent());
    }

    public function test_owner_passes_with_raw_id(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        $response = (new EnsureStockOwnership)->handle(
            $this->request($user, "s/{$stock->id}"),
            fn () => response('ok')
        );

        $this->assertSame('ok', $response->getContent());
    }

    public function test_non_owner_is_forbidden(): void
    {
        $stock = Stock::factory()->create();
        $this->expectException(HttpException::class);

        (new EnsureStockOwnership)->handle(
            $this->request(User::factory()->create(), "s/{$stock->id}", $stock),
            fn () => response('ok')
        );
    }

    public function test_guest_is_forbidden(): void
    {
        $stock = Stock::factory()->create();
        $this->expectException(HttpException::class);

        (new EnsureStockOwnership)->handle($this->request(null, "s/{$stock->id}", $stock), fn () => response('ok'));
    }
}
