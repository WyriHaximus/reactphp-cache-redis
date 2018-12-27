<?php declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use Clue\React\Redis\Client;
use Evenement\EventEmitter;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class ClientStub extends EventEmitter implements Client
{
    public function __call($name, $args): void
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

    public function exists(): PromiseInterface
    {
        return resolve();
    }

    public function get(): PromiseInterface
    {
        return resolve();
    }

    public function set(): PromiseInterface
    {
        return resolve();
    }

    public function expire(): PromiseInterface
    {
        return resolve();
    }

    public function del(): PromiseInterface
    {
        return resolve();
    }
}
