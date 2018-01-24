<?php declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;
use function React\Promise\reject;

final class Redis implements CacheInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $ttl;

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
     * @return PromiseInterface
     */
    public function get($key)
    {
        return $this->client->exists($this->prefix . $key)->then(function ($result) use ($key) {
            if ($result == false) {
                return reject();
            }

            return $this->client->get($this->prefix . $key);
        });
    }

    /**
     * @param  string           $key
     * @param  mixed            $value
     * @return PromiseInterface
     */
    public function set($key, $value)
    {
        if ($this->ttl === 0) {
            return $this->client->set($this->prefix . $key, $value);
        }

        return $this->client->set($this->prefix . $key, $value)->then(function () use ($key) {
            return $this->client->expire($this->prefix . $key, $this->ttl);
        });
    }

    /**
     * @param  string           $key
     * @return PromiseInterface
     */
    public function remove($key)
    {
        return $this->client->del($this->prefix . $key);
    }
}
