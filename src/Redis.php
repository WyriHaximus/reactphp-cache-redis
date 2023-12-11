<?php

declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;

use function preg_quote;
use function React\Promise\all;
use function React\Promise\resolve;
use function Safe\preg_replace;

final class Redis implements CacheInterface
{
    /** @phpstan-ignore-next-line */
    public function __construct(private Client $client, private string $prefix = 'react:cache:', private int $ttl = 0)
    {
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function get($key, $default = null): PromiseInterface
    {
        /**
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress TooManyTemplateParams
         */
        return $this->has($key)->then(function ($result) use ($key): PromiseInterface {
            if ($result === false) {
                return resolve(null);
            }

            /** @phpstan-ignore-next-line */
            return $this->client->get($this->prefix . $key);
        });
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function set($key, $value, $ttl = null): PromiseInterface
    {
        if ($this->ttl === 0 && $ttl === null) {
            /** @phpstan-ignore-next-line */
            return $this->client->set($this->prefix . $key, (string) $value)->then(
                static fn (): PromiseInterface => resolve(true),
                static fn (): PromiseInterface => resolve(false),
            );
        }

        /** @phpstan-ignore-next-line */
        return $this->client->psetex(
            $this->prefix . $key,
            (string) ((float) ($this->ttl > 0 ? $this->ttl : $ttl) * 1000),
            (string) $value, /** @phpstan-ignore-line */
        )->then(
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );
    }

    /** @inheritDoc */
    public function delete($key): PromiseInterface
    {
        /** @phpstan-ignore-next-line */
        return $this->client->del($this->prefix . $key)->then(
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function getMultiple(array $keys, $default = null)
    {
        $promises = [];
        foreach ($keys as $key) {
            $promises[$key] = $this->get($key, $default);
        }

        return all($promises);
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function setMultiple(array $values, $ttl = null)
    {
        $promises = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) {
            $promises[$key] = $this->set((string) $key, $value, $ttl);
        }

        return all($promises);
    }

    /** @inheritDoc */
    public function deleteMultiple(array $keys)
    {
        foreach ($keys as $index => $key) {
            $keys[$index] = $this->prefix . $key;
        }

        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         */
        return $this->client->del(...$keys)->then(
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );
    }

    /** @inheritDoc */
    public function clear()
    {
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress TooManyTemplateParams
         */
        return $this->client->keys($this->prefix . '*')->then(function (array $keys): PromiseInterface {
            /** @var array<string> $keys */
            $keys = preg_replace('|^' . preg_quote($this->prefix) . '|', '', $keys);

            return $this->deleteMultiple($keys);
        });
    }

    /** @inheritDoc */
    public function has($key)
    {
        /** @phpstan-ignore-next-line */
        return $this->client->exists($this->prefix . $key);
    }
}
