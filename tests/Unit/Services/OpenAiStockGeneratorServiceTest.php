<?php

namespace Tests\Unit\Services;

use App\Features\Ai\Clients\OpenAiClient;
use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiStockGeneratorServiceTest extends TestCase
{
    private function service(string $response): OpenAiStockGeneratorService
    {
        $client = $this->createStub(AiClientInterface::class);
        $client->method('generate')->willReturn($response);

        return new OpenAiStockGeneratorService($client);
    }

    public function test_parses_plain_json_array(): void
    {
        $items = $this->service('[{"name":"Kertas","quantity":3},"bukan-array"]')->generate('kantor', 2);

        $this->assertCount(1, $items);
        $this->assertContainsOnlyInstancesOf(GeneratedStockItem::class, $items);
        $this->assertSame('Kertas', $items[0]->name);
    }

    public function test_parses_fenced_json_with_items_key(): void
    {
        $items = $this->service("```json\n{\"items\":[{\"name\":\"A\"},{\"name\":\"B\"}]}\n```")->generate('x');

        $this->assertCount(2, $items);
    }

    public function test_prompt_contains_topic_and_count(): void
    {
        $client = $this->createMock(AiClientInterface::class);
        $client->expects($this->once())
            ->method('generate')
            ->with($this->callback(fn ($p) => str_contains($p, 'bahan kue') && str_contains($p, '7')))
            ->willReturn('[]');

        $this->assertSame([], (new OpenAiStockGeneratorService($client))->generate('bahan kue', 7));
    }

    public function test_invalid_json_throws(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service('bukan json')->generate('x');
    }

    public function test_openai_client_calls_api(): void
    {
        config(['openai.api_key' => 'sk-test', 'openai.model' => 'gpt-test', 'openai.base_url' => 'https://api.example.test/v1/']);
        Http::fake([
            'api.example.test/*' => Http::response(['choices' => [['message' => ['content' => '[]']]]]),
        ]);

        $this->assertSame('[]', (new OpenAiClient)->generate('halo'));

        Http::assertSent(fn ($request) => $request->url() === 'https://api.example.test/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer sk-test')
            && $request['model'] === 'gpt-test');
    }

    public function test_openai_client_throws_on_http_error(): void
    {
        config(['openai.base_url' => 'https://api.example.test/v1']);
        Http::fake(['*' => Http::response(['error' => 'x'], 500)]);

        $this->expectException(RequestException::class);

        (new OpenAiClient)->generate('halo');
    }

    public function test_openai_client_returns_empty_string_when_no_content(): void
    {
        config(['openai.base_url' => 'https://api.example.test/v1']);
        Http::fake(['*' => Http::response([])]);

        $this->assertSame('', (new OpenAiClient)->generate('halo'));
    }
}
