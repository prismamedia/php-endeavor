<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Strategy;

class ConstantStrategy extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function getDelay(int $attempt): int
    {
        return $this->delay;
    }
}
