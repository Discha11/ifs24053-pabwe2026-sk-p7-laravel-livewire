<?php

namespace App\Features\Ai\Contracts;

interface AiClientInterface
{
    /**
     * Kirim prompt ke model AI dan kembalikan teks jawabannya.
     */
    public function generate(string $prompt): string;
}
