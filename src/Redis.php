<?php

declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;
use RuntimeException;

use function preg_last_error;
use function preg_last_error_msg;
use function preg_quote;
use function preg_replace;
use function React\Promise\all;
use function React\Promise\resolve;

use const PREG_NO_ERROR;

final class Redis implements CacheInterface
{
    private const string DEFAULT_PREFIX = 'react:cache:';
    private const int DEFAULT_TTL       = 0;

    /** @phpstan-ignore-next-line */
    public function __construct(private Client $client, private string $prefix = self::DEFAULT_PREFIX, private int $ttl = self::DEFAULT_TTL)
    {
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function get($key, $default = null): PromiseInterface
    {
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
        foreach ($values as $key => $value) {
            $promises[$key] = $this->set((string) $key, $value, $ttl);
        }

        /** @param PromiseInterface<bool> $bools */
        return all($promises)->then(static function (array $bools): bool {
            foreach ($bools as $bool) {
                if (! $bool) {
                    return false;
                }
            }

            return true;
        });
    }

    /** @inheritDoc */
    public function deleteMultiple(array $keys)
    {
        foreach ($keys as $index => $key) {
            $keys[$index] = $this->prefix . $key;
        }

        return $this->client->del(...$keys)->then( /** @phpstan-ignore-line */
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );
    }

    /** @inheritDoc */
    public function clear()
    {
        return $this->client->keys($this->prefix . '*')->then( /** @phpstan-ignore-line */
            function (array $keys): PromiseInterface {
                /** @var array<string> $matchedKeys */
                $matchedKeys = preg_replace('|^' . preg_quote($this->prefix) . '|', '', $keys);
                if (preg_last_error() !== PREG_NO_ERROR) {
                    throw new RuntimeException(preg_last_error_msg());
                }

                return $this->deleteMultiple($matchedKeys);
            },
        );
    }

    /** @inheritDoc */
    public function has($key)
    {
        /** @phpstan-ignore-next-line */
        return $this->client->exists($this->prefix . $key);
    }
}
