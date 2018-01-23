<?php

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;
use function React\Promise\reject;

class Redis implements CacheInterface
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
    public function __construct(Client $client, $prefix = 'reach:cache:', $ttl = 0)
    {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->ttl = (int)$ttl;
    }

    /**
     * @param string $key
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
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        if ($this->ttl === 0) {
            $this->client->set($this->prefix . $key, $value);

            return;
        }

        $this->client->set($this->prefix . $key, $value)->then(function () use ($key) {
            $this->client->expire($this->prefix . $key, $this->ttl);
        });
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        $this->client->del($this->prefix . $key);
    }
}
