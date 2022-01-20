<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Strategy;

abstract class AbstractStrategy implements StrategyInterface
{
    protected int $delay;

    public function __construct(int $delay = 100)
    {
        $this->delay = $delay;
    }
}
