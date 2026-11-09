<?php

namespace App\Features\Ai\Clients;

use App\Features\Ai\Contracts\AiClientInterface;
use Illuminate\Support\Facades\Http;

class OpenAiClient implements AiClientInterface
{
    public function generate(string $prompt): string
    {
        $response = Http::withToken((string) config('openai.api_key'))
            ->timeout(60)
            ->post(rtrim((string) config('openai.base_url'), '/').'/chat/completions', [
                'model' => config('openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu asisten inventaris. Jawab hanya dengan JSON valid.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ])
            ->throw();

        return (string) $response->json('choices.0.message.content', '');
    }
}
