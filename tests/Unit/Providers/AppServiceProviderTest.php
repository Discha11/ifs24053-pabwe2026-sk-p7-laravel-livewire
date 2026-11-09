<?php

namespace Tests\Unit\Providers;

use App\Features\Ai\Clients\OpenAiClient;
use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_bindings(): void
    {
        $this->assertInstanceOf(StockRepository::class, app(StockRepositoryInterface::class));
        $this->assertInstanceOf(OpenAiClient::class, app(AiClientInterface::class));
    }

    public function test_rate_limiter_is_1000_per_second(): void
    {
        $limiter = RateLimiter::limiter('1000-per-second');
        $this->assertNotNull($limiter);

        $guest = $limiter(Request::create('/'));
        $this->assertInstanceOf(Limit::class, $guest);
        $this->assertSame(1000, $guest->maxAttempts);
        $this->assertSame(1, $guest->decaySeconds);

        $request = Request::create('/');
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => 42]));
        $this->assertSame(42, $limiter($request)->key);
    }

    public function test_force_https(): void
    {
        config(['app.force_https' => true]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/'));
    }
}
