<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    private function request(?User $user): Request
    {
        $request = Request::create('/x');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_allows_matching_role(): void
    {
        $response = (new EnsureRole)->handle(
            $this->request(new User(['role' => 'admin'])),
            fn () => response('ok'),
            'admin'
        );

        $this->assertSame('ok', $response->getContent());
    }

    public function test_blocks_other_role(): void
    {
        $this->expectException(HttpException::class);

        (new EnsureRole)->handle($this->request(new User(['role' => 'member'])), fn () => response('ok'), 'admin');
    }

    public function test_blocks_guest(): void
    {
        $this->expectException(HttpException::class);

        (new EnsureRole)->handle($this->request(null), fn () => response('ok'), 'member');
    }
}
