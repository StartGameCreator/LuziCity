<?php

namespace App\Services\AI;

use App\Models\AiProvider;

class AiCostCalculator
{
    public function micros(?AiProvider $provider, int $inputTokens, int $outputTokens): int
    {
        if (! $provider) return 0;
        $input = $this->decimalToMicros((string) ($provider->input_cost_per_million ?? '0'));
        $output = $this->decimalToMicros((string) ($provider->output_cost_per_million ?? '0'));
        return intdiv(($input * max(0, $inputTokens)) + ($output * max(0, $outputTokens)), 1_000_000);
    }

    public function decimal(int $micros): string
    {
        return number_format($micros / 1_000_000, 6, '.', '');
    }

    private function decimalToMicros(string $value): int
    {
        $parts = explode('.', preg_replace('/[^\d.]/', '', $value), 2);
        return ((int) ($parts[0] ?: 0) * 1_000_000) + (int) str_pad(substr($parts[1] ?? '', 0, 6), 6, '0');
    }
}
