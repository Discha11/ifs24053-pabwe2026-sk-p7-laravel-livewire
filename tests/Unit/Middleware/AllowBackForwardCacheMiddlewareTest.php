<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AllowBackForwardCache;
use Illuminate\Http\Request;
use Tests\TestCase;

class AllowBackForwardCacheMiddlewareTest extends TestCase
{
    public function test_strips_no_store_on_get_page_responses(): void
    {
        $response = (new AllowBackForwardCache)->handle(
            Request::create('/login', 'GET'),
            fn () => response('ok')->header('Cache-Control', 'no-cache, must-revalidate, no-store, max-age=0, private'),
        );

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringNotContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
    }

    public function test_leaves_non_no_store_get_responses_untouched(): void
    {
        $response = (new AllowBackForwardCache)->handle(
            Request::create('/login', 'GET'),
            fn () => response('ok')->header('Cache-Control', 'public, max-age=60'),
        );

        $this->assertSame('max-age=60, public', (string) $response->headers->get('Cache-Control'));
    }

    public function test_leaves_livewire_and_post_requests_untouched(): void
    {
        $livewireRequest = Request::create('/livewire/update', 'GET');
        $livewireRequest->headers->set('X-Livewire', 'true');

        $livewireResponse = (new AllowBackForwardCache)->handle(
            $livewireRequest,
            fn () => response('ok')->header('Cache-Control', 'no-store, private'),
        );

        $postResponse = (new AllowBackForwardCache)->handle(
            Request::create('/logout', 'POST'),
            fn () => response('ok')->header('Cache-Control', 'no-store, private'),
        );

        $this->assertStringContainsString('no-store', (string) $livewireResponse->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $postResponse->headers->get('Cache-Control'));
    }
}
