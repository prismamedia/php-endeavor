<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Tests\Strategy;

use PHPUnit\Framework\TestCase;
use PrismaMedia\Endeavor\Strategy\LinearStrategy;

class LinearStrategyTest extends TestCase
{
    public function testDefault(): void
    {
        $strategy = new LinearStrategy();

        self::assertSame(100, $strategy->getDelay(1));
    }

    public function testGetDelay(): void
    {
        $strategy = new LinearStrategy(1000);

        self::assertSame(1000, $strategy->getDelay(1));
        self::assertSame(2000, $strategy->getDelay(2));
        self::assertSame(3000, $strategy->getDelay(3));
        self::assertSame(4000, $strategy->getDelay(4));
        self::assertSame(5000, $strategy->getDelay(5));
    }
}
