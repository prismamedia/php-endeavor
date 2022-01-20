<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Strategy;

class MultiplicativeStrategy extends AbstractStrategy
{
    protected float $multiplier;

    public function __construct(int $delay = 100, float $multiplier = 2.0)
    {
        if ($multiplier < 1) {
            throw new \InvalidArgumentException(sprintf('Multiplier must be greater than or equal to 1: "%s" given.', $multiplier));
        }

        $this->multiplier = $multiplier;

        parent::__construct($delay);
    }

    /**
     * {@inheritDoc}
     */
    public function getDelay(int $attempt): int
    {
        return (int) ($this->delay * $this->multiplier ** ($attempt - 1));
    }
}
