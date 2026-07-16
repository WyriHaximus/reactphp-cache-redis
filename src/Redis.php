<?php

declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;
use RuntimeException;

use function array_all;
use function preg_last_error;
use function preg_last_error_msg;
use function preg_quote;
use function preg_replace;
use function React\Promise\all;
use function React\Promise\resolve;

use const PREG_NO_ERROR;

/** @api */
final readonly class Redis implements CacheInterface
{
    private const string DEFAULT_PREFIX = 'react:cache:';
    private const int DEFAULT_TTL       = 0;

    /** @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue,ergebnis.noConstructorParameterWithDefaultValue */
    public function __construct(private Client $client, private string $prefix = self::DEFAULT_PREFIX, private int $ttl = self::DEFAULT_TTL)
    {
    }

    /**
     * @inheritDoc
     * @phpstan-ignore ergebnis.noParameterWithNullDefaultValue
     */
    public function get($key, $default = null): PromiseInterface
    {
        return $this->has($key)->then(function (mixed $result) use ($key): PromiseInterface {
            if ($result === false) {
                return resolve(null);
            }

            /** @phpstan-ignore method.notFound,return.type */
            return $this->client->get($this->prefix . $key);
        });
    }

    /**
     * @inheritDoc
     * @phpstan-ignore ergebnis.noParameterWithNullDefaultValue
     */
    public function set($key, $value, $ttl = null): PromiseInterface
    {
        if ($this->ttl === 0 && $ttl === null) {
            /**
             * @var PromiseInterface<bool> $created
             * @phpstan-ignore method.notFound,cast.string,method.nonObject
             */
            $created = $this->client->set($this->prefix . $key, (string) $value)->then(
                static fn (): PromiseInterface => resolve(true),
                static fn (): PromiseInterface => resolve(false),
            );

            return $created;
        }

        /**
         * @var PromiseInterface<bool> $created
         * @phpstan-ignore method.notFound
         */
        $created = $this->client->psetex(
            $this->prefix . $key,
            (string) ((float) ($this->ttl > 0 ? $this->ttl : $ttl) * 1000),
            /** @phpstan-ignore cast.string */
            (string) $value,
            /** @phpstan-ignore method.nonObject */
        )->then(
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );

        return $created;
    }

    /** @inheritDoc */
    public function delete($key): PromiseInterface
    {
        /**
         * @var PromiseInterface<bool> $deleted
         * @phpstan-ignore method.notFound,method.nonObject
         */
        $deleted = $this->client->del($this->prefix . $key)->then(
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );

        return $deleted;
    }

    /**
     * @inheritDoc
     * @phpstan-ignore typeCoverage.returnTypeCoverage,shipmonk.missingNativeReturnTypehint,missingType.iterableValue,ergebnis.noParameterWithNullDefaultValue
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
     * @phpstan-ignore typeCoverage.returnTypeCoverage,shipmonk.missingNativeReturnTypehint,ergebnis.noParameterWithNullDefaultValue,missingType.iterableValue
     */
    public function setMultiple(array $values, $ttl = null)
    {
        $promises = [];
        foreach ($values as $key => $value) {
            $promises[$key] = $this->set($key, $value, $ttl);
        }

        /** @param PromiseInterface<bool> $bools */
        return all($promises)->then(static fn (array $bools): bool => array_all($bools, static fn (bool $bool): bool => $bool));
    }

    /**
     * @inheritDoc
     * @phpstan-ignore typeCoverage.returnTypeCoverage,shipmonk.missingNativeReturnTypehint
     */
    public function deleteMultiple(array $keys)
    {
        foreach ($keys as $index => $key) {
            $keys[$index] = $this->prefix . $key;
        }

        /**
         * @var PromiseInterface<bool> $deleted
         * @phpstan-ignore method.notFound,method.nonObject
         */
        $deleted = $this->client->del(...$keys)->then(
            static fn (): PromiseInterface => resolve(true),
            static fn (): PromiseInterface => resolve(false),
        );

        return $deleted;
    }

    /**
     * @inheritDoc
     * @phpstan-ignore typeCoverage.returnTypeCoverage,shipmonk.missingNativeReturnTypehint
     */
    public function clear()
    {
        /**
         * @var PromiseInterface<bool> $cleared
         * @phpstan-ignore method.notFound,method.nonObject
         */
        $cleared = $this->client->keys($this->prefix . '*')->then(
            function (array $keys): PromiseInterface {
                /**
                 * @var array<string> $matchedKeys
                 * @phpstan-ignore argument.type
                 */
                $matchedKeys = preg_replace('|^' . preg_quote($this->prefix) . '|', '', $keys);
                if (preg_last_error() !== PREG_NO_ERROR) {
                    throw new RuntimeException(preg_last_error_msg());
                }

                return $this->deleteMultiple($matchedKeys);
            },
        );

        return $cleared;
    }

    /**
     * @inheritDoc
     * @phpstan-ignore typeCoverage.returnTypeCoverage,shipmonk.missingNativeReturnTypehint
     */
    public function has($key)
    {
        /**
         * @var PromiseInterface<bool> $exists
         * @phpstan-ignore method.notFound
         */
        $exists = $this->client->exists($this->prefix . $key);

        return $exists;
    }
}
