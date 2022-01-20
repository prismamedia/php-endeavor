<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Tests\Strategy;

use PHPUnit\Framework\TestCase;
use PrismaMedia\Endeavor\Strategy\MultiplicativeStrategy;

class MultiplicativeStrategyTest extends TestCase
{
    public function testDefault(): void
    {
        $strategy = new MultiplicativeStrategy();

        self::assertSame(100, $strategy->getDelay(1));
    }

    public function testGetDelay(): void
    {
        $strategy = new MultiplicativeStrategy(1000, 1.5);

        self::assertSame(1000, $strategy->getDelay(1));
        self::assertSame(1500, $strategy->getDelay(2));
        self::assertSame(2250, $strategy->getDelay(3));
        self::assertSame(3375, $strategy->getDelay(4));
        self::assertSame(5062, $strategy->getDelay(5));
    }

    public function testGetDelayWithInvalidMultiplier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiplier must be greater than or equal to 1: "0.5" given.');

        new MultiplicativeStrategy(1000, 0.5);
    }
}
