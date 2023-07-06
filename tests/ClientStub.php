<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use Clue\React\Redis\Client;
use Evenement\EventEmitterTrait;
use React\Promise\PromiseInterface;

use function React\Promise\resolve;

/** @phpstan-ignore-next-line */
class ClientStub implements Client
{
    use EventEmitterTrait;

    /** @phpstan-ignore-next-line */
    public function __call($name, $args): void //phpcs:disabled
    {
        // TODO: Implement __call() method.
    }

    public function end(): void
    {
        // TODO: Implement end() method.
    }

    public function close(): void
    {
        // TODO: Implement close() method.
    }

    /**
     * @return PromiseInterface<bool>
     */
    public function exists(): PromiseInterface
    {
        return resolve(true);
    }

    /**
     * @return PromiseInterface<mixed>
     */
    public function get(): PromiseInterface
    {
        return resolve(null);
    }

    /**
     * @return PromiseInterface<null>
     */
    public function set(): PromiseInterface
    {
        return resolve(null);
    }

    /**
     * @return PromiseInterface<null>
     */
    public function psetex(): PromiseInterface
    {
        return resolve(null);
    }

    /**
     * @return PromiseInterface<null>
     */
    public function del(): PromiseInterface
    {
        return resolve(null);
    }

    /**
     * @return PromiseInterface<array<string>>
     */
    public function keys(): PromiseInterface
    {
        return resolve(['string']);
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function isBusy() //phpcs:disabled
    {
        return true;
    }
}
