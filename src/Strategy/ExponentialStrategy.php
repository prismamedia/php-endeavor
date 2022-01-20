<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Strategy;

class ExponentialStrategy extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function getDelay(int $attempt): int
    {
        return (int) ($this->delay * 2 ** ($attempt - 1));
    }
}
