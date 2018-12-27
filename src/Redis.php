<?php declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class Redis implements CacheInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    private $ttl;

    /**
     * Redis constructor.
     * @param Client $client
     * @param string $prefix
     */
    public function __construct(Client $client, string $prefix = 'reach:cache:', int $ttl = 0)
    {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    /**
     * @param  string           $key
     * @param  null|mixed       $default
     * @return PromiseInterface
     */
    public function get($key, $default = null)
    {
        return $this->client->exists($this->prefix . $key)->then(function ($result) use ($key) {
            if ($result == false) {
                return resolve(null);
            }

            return $this->client->get($this->prefix . $key);
        });
    }

    /**
     * @param  string           $key
     * @param  mixed            $value
     * @param  ?float           $ttl
     * @return PromiseInterface
     */
    public function set($key, $value, $ttl = null)
    {
        if ($this->ttl === 0 && $ttl === null) {
            return $this->client->set($this->prefix . $key, $value)->then(function () {
                return resolve(true);
            }, function () {
                return resolve(false);
            });
        }

        return $this->client->set($this->prefix . $key, $value)->then(function () use ($key, $ttl) {
            return $this->client->expire($this->prefix . $key, $this->ttl > 0 ? $this->ttl : $ttl);
        })->then(function () {
            return resolve(true);
        }, function () {
            return resolve(false);
        });
    }

    /**
     * @param  string           $key
     * @return PromiseInterface
     */
    public function delete($key)
    {
        return $this->client->del($this->prefix . $key)->then(function () {
            return resolve(true);
        }, function () {
            return resolve(false);
        });
    }
}
