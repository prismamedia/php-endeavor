<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Tests\Strategy;

use PHPUnit\Framework\TestCase;
use PrismaMedia\Endeavor\Strategy\ConstantStrategy;

class ConstantStrategyTest extends TestCase
{
    public function testDefault(): void
    {
        $strategy = new ConstantStrategy();

        self::assertSame(100, $strategy->getDelay(1));
    }

    public function testGetDelay(): void
    {
        $strategy = new ConstantStrategy(1000);

        self::assertSame(1000, $strategy->getDelay(1));
        self::assertSame(1000, $strategy->getDelay(2));
        self::assertSame(1000, $strategy->getDelay(3));
        self::assertSame(1000, $strategy->getDelay(4));
        self::assertSame(1000, $strategy->getDelay(5));
    }
}
