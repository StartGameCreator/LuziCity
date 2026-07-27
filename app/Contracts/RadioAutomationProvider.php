<?php

namespace App\Contracts;

interface RadioAutomationProvider
{
    public function enabled(): bool;

    public function health(): array;

    public function nowPlaying(): ?array;

    public function station(): ?array;

    public function control(string $action): array;
}
