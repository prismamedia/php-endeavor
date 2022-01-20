# Endeavor (PHP)

Endeavor is a PHP utility to retry anything, using various strategies.


## Requirements

- PHP 7.4+


## Installation

Using [Composer](https://getcomposer.org/):

```bash
composer require prismamedia/php-endeavor
```


## Usage

Simply wrap the code you want to retry in a `Closure` in the `run()` method:

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\ConstantStrategy;

$endeavor = new Endeavor(new ConstantStrategy(500));
$endeavor->run(function () {
    // Code which can throw any \Throwable
});
```

By default, Endeavor will try to run the given code 5 times. If the first attempt is successful, Endeavor will stop.
But if the code fails at each attempt, Endeavor will throw an exception on the last attempt.

The maximum number of attempts can be specified using the second argument of the constructor:

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\ConstantStrategy;

$endeavor = new Endeavor(new ConstantStrategy(500), 3);
$endeavor->run(function () {
    // Code which can throw any \Throwable
});
```

In this example, it will run the code **3 times** and throw on the third attempt if it continues to fail.

Keep in mind that the maximum number of attempts includes the first.

A maximum delay can be specified to create a ceiling using the third argument of the constructor:

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\ExponentialStrategy;

$endeavor = new Endeavor(new ExponentialStrategy(1000), 5, 5000);
$endeavor->run(function () {
    // Code which can throw any \Throwable
});
```

In this example, the code will be executed **5 times** with an exponential strategy which doubles the delay
at each attempt to a maximum of **5 seconds**.

## Strategies

Endeavor can be instantiated with various strategies, depending on the expected retry interval.

Each strategy takes a required `delay` in milliseconds and compute the next attempt delay based on
the nature of the strategy.

### ConstantStrategy

This is the simplest strategy. It takes a fixed delay and sets the interval between attempt to that number.

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\ConstantStrategy;

$endeavor = new Endeavor(new ConstantStrategy(100));
$endeavor->run(function () {
    throw new \RuntimeException('Failing');
});

// 1st attempt: immediate
// 2nd attempt: 100ms
// 3rd attempt: 100ms
// 4th attempt: 100ms
// 5th attempt: 100ms
```


### LinearStrategy

This strategy takes an initial delay and adds it up at each attempt.

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\LinearStrategy;

$endeavor = new Endeavor(new LinearStrategy(100));
$endeavor->run(function () {
    throw new \RuntimeException('Failing');
});

// 1st attempt: immediate
// 2nd attempt: 100ms
// 3rd attempt: 200ms
// 4th attempt: 300ms
// 5th attempt: 400ms
```


### ExponentialStrategy

This strategy takes an initial delay and doubles it at each attempt.

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\ExponentialStrategy;

$endeavor = new Endeavor(new ExponentialStrategy(100));
$endeavor->run(function () {
    throw new \RuntimeException('Failing');
});

// 1st attempt: immediate
// 2nd attempt: 100ms
// 3rd attempt: 200ms
// 4th attempt: 400ms
// 5th attempt: 800ms
```


### MultiplicativeStrategy

This strategy takes an initial delay and a multiplier then multiplies the delay at each attempt.

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\MultiplicativeStrategy;

$endeavor = new Endeavor(new MultiplicativeStrategy(100, 3));
$endeavor->run(function () {
    throw new \RuntimeException('Failing');
});

// 1st attempt: immediate
// 2nd attempt: 100ms
// 3rd attempt: 300ms
// 4th attempt: 900ms
// 5th attempt: 2700ms
```


## Error handling

By default, Endeavor will simply retry the code when an Exception is thrown,
then throw the last encountered Exception when reaching the maximum number of attempts.

An error handler can be specified using a `Closure` which will be executed after each unsuccessful attempt.

It can be used for logging purpose:

```php
use PrismaMedia\Endeavor\Endeavor;
use PrismaMedia\Endeavor\Strategy\LinearStrategy;

$endeavor = new Endeavor(new LinearStrategy(500));
$endeavor->setErrorHandler(function (Endeavor $endeavor, \Throwable $e, int $attempt) {
    // $endeavor is the current instance
    // $e is the throw Exception during this attempt
    // $attempt is the current attempt number
    $this->logger->error(
        'Something went wrong on the attempt #{attempt}: {error}',
        [
            'attempt' => $attempt,
            'error' => $e->getMessage(),
        ]
    );
});
$endeavor->run(function () {
    throw new \RuntimeException('Failing');
});
```

Or even throwing another Exception and stopping Endeavor if the error is unrecoverable:

```php
$endeavor->setErrorHandler(function (Endeavor $endeavor, \Throwable $e, int $attempt) {
    if ($e instanceof OneSpecificException) {
        throw $e
    }
});
```

It can also be used to change the current strategy:

```php
$endeavor->setErrorHandler(function (Endeavor $endeavor, \Throwable $e, int $attempt) {
    if ($e instanceof UnreachableDatabaseException) {
        $endeavor->setStrategy(new ConstantStrategy(5000));
    }
});
```


## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.


## License

[MIT](https://choosealicense.com/licenses/mit/)
