<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use RuntimeException;

class OpenAiStockGeneratorService
{
    public function __construct(private readonly AiClientInterface $client) {}

    /**
     * @return array<int, GeneratedStockItem>
     */
    public function generate(string $topic, int $count = 5): array
    {
        $raw = $this->client->generate($this->buildPrompt($topic, $count));
        $raw = trim((string) preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));

        $data = json_decode($raw, true);

        if (! is_array($data)) {
            throw new RuntimeException('Respons AI tidak valid.');
        }

        $data = $data['items'] ?? $data;

        return array_values(array_map(
            fn (array $item) => GeneratedStockItem::fromArray($item),
            array_filter($data, 'is_array')
        ));
    }

    private function buildPrompt(string $topic, int $count): string
    {
        return "Buatkan {$count} item inventaris/stok barang untuk: \"{$topic}\". "
            .'Balas HANYA dengan JSON array. Tiap elemen punya kunci: '
            .'name (string), sku (string), description (string, boleh Markdown), '
            .'quantity (integer), unit (string, mis. pcs/box/kg), price (angka rupiah), min_stock (integer).';
    }
}
