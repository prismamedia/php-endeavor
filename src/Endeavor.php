<?php

declare(strict_types=1);

namespace PrismaMedia\Endeavor;

use PrismaMedia\Endeavor\Strategy\StrategyInterface;

class Endeavor
{
    protected StrategyInterface $strategy;
    protected int $maxAttempts;
    protected ?int $maxDelay;
    protected ?\Closure $errorHandler = null;

    public function __construct(StrategyInterface $strategy, int $maxAttempts = 5, ?int $maxDelay = null)
    {
        $this->setStrategy($strategy);
        $this->setMaxAttempts($maxAttempts);
        $this->setMaxDelay($maxDelay);
    }

    public function getStrategy(): StrategyInterface
    {
        return $this->strategy;
    }

    public function setStrategy(StrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function getMaxDelay(): ?int
    {
        return $this->maxDelay;
    }

    public function setMaxDelay(?int $maxDelay = null): self
    {
        $this->maxDelay = $maxDelay;

        return $this;
    }

    /**
     * @param \Closure(self, \Throwable, int): void $closure
     */
    public function setErrorHandler(\Closure $closure): self
    {
        $this->errorHandler = $closure;

        return $this;
    }

    public function run(callable $callable): void
    {
        $attempt = 0;

        while ($attempt++ <= $this->maxAttempts) {
            $exception = null;

            try {
                $callable();

                break;
            } catch (\Throwable $e) {
                $exception = $e;
            }

            if (null !== $this->errorHandler) {
                ($this->errorHandler)($this, $exception, $attempt);
            }

            if ($attempt >= $this->maxAttempts) {
                throw $exception;
            }

            $this->wait($attempt);
        }
    }

    public function getDelay(int $attempt): int
    {
        $delay = $this->strategy->getDelay($attempt);

        if (null !== $this->maxDelay) {
            return min($delay, $this->maxDelay);
        }

        return $delay;
    }

    protected function wait(int $attempt): void
    {
        usleep($this->getDelay($attempt) * 1000);
    }
}
