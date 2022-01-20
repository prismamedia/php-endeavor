<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor\Tests;

use PHPUnit\Framework\TestCase;
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\ConstantStrategy;
use PrismaMedia\Endeavor\Strategy\LinearStrategy;

class EndeavorTest extends TestCase
{
    public function testConstruct(): void
    {
        $strategy = new LinearStrategy();
        $endeavor = new Endeavor($strategy);

        self::assertSame($strategy, $endeavor->getStrategy());
        self::assertSame(5, $endeavor->getMaxAttempts());
        self::assertNull($endeavor->getMaxDelay());

        $endeavor = new Endeavor($strategy, 42, 500);

        self::assertSame($strategy, $endeavor->getStrategy());
        self::assertSame(42, $endeavor->getMaxAttempts());
        self::assertSame(500, $endeavor->getMaxDelay());
    }

    public function testSetters(): void
    {
        $strategy = new ConstantStrategy(100);
        $endeavor = new Endeavor(new LinearStrategy());
        $endeavor->setStrategy($strategy);
        $endeavor->setMaxAttempts(3);
        $endeavor->setMaxDelay(123);

        self::assertSame($strategy, $endeavor->getStrategy());
        self::assertSame(3, $endeavor->getMaxAttempts());
        self::assertSame(123, $endeavor->getMaxDelay());
    }

    public function testRun(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));

        $result = null;
        $endeavor->run(function () use (&$result) {
            $result = 'success';
        });

        self::assertSame('success', $result);
    }

    public function testRunWithFailure(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('fail');

        $endeavor->run(function () {
            throw new \Exception('fail');
        });
    }

    public function testRunAttempts(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));

        $result = null;
        $attempts = 0;

        $endeavor->run(function () use (&$result, &$attempts) {
            ++$attempts;

            if ($attempts < 5) {
                throw new \Exception('fail');
            }

            $result = 'success';
        });

        self::assertSame('success', $result);
        self::assertSame(5, $attempts);
    }

    public function testRunWithErrorHandler(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));

        $logs = [];
        $endeavor->setErrorHandler(function (Endeavor $endeavor, \Throwable $e, int $attempt) use (&$logs) {
            $logs[] = "[Attempt=$attempt, MaxAttempt={$endeavor->getMaxAttempts()}] {$e->getMessage()}";
        });

        $result = null;
        $attempts = 0;

        $endeavor->run(function () use (&$result, &$attempts) {
            ++$attempts;

            if ($attempts < 5) {
                throw new \Exception('fail');
            }

            $result = 'success';
        });

        self::assertSame('success', $result);
        self::assertSame(5, $attempts);
        self::assertCount(4, $logs);
        self::assertSame('[Attempt=4, MaxAttempt=5] fail', array_pop($logs));
    }

    public function testRunWithErrorHandlerThatShouldNotBeCalled(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));
        $endeavor->setErrorHandler(function () {
            throw new \Exception('Should not be called!');
        });

        $result = null;
        $endeavor->run(function () use (&$result) {
            $result = 'success';
        });

        self::assertSame('success', $result);
    }

    public function testRunWithErrorHandlerThatStopTheRun(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));

        $result = null;
        $endeavor->setErrorHandler(function (Endeavor $endeavor, \Throwable $e, int $attempt) use (&$result) {
            $result = 'failed!';

            if ($attempt >= 3) {
                $endeavor->setMaxAttempts(0);
            }
        });

        $attempts = 0;
        try {
            $endeavor->run(function () use (&$attempts) {
                ++$attempts;

                throw new \Exception('fail');
            });
        } catch (\Throwable $e) {
            self::assertInstanceOf(\Exception::class, $e);
            self::assertSame('fail', $e->getMessage());
        }

        self::assertSame(3, $attempts);
        self::assertSame('failed!', $result);
    }

    public function testRunWithErrorHandlerThatChangesStrategy(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(0));

        $attempts = 0;
        $endeavor->setErrorHandler(function (Endeavor $endeavor, \Throwable $e, int $attempt) {
            if ($attempt > 2) {
                $endeavor->setStrategy(new ConstantStrategy(0));
            }
        });

        try {
            $endeavor->run(function () use (&$attempts) {
                ++$attempts;

                throw new \Exception('fail');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        self::assertSame(5, $attempts);
        self::assertInstanceOf(ConstantStrategy::class, $endeavor->getStrategy());
    }

    public function testGetDelay(): void
    {
        $endeavor = new Endeavor(new LinearStrategy(), 5, 250);

        self::assertSame(100, $endeavor->getDelay(1));
        self::assertSame(200, $endeavor->getDelay(2));
        self::assertSame(250, $endeavor->getDelay(3));
        self::assertSame(250, $endeavor->getDelay(4));
        self::assertSame(250, $endeavor->getDelay(5));
    }
}
