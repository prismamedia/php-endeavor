<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Strategy;

interface StrategyInterface
{
    /**
     * Return the computed delay for this attempt.
     */
    public function getDelay(int $attempt): int;
}
