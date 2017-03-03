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
     * Redis constructor.
     * @param Client $client
     * @param string $prefix
     */
    public function __construct(Client $client, $prefix = 'reach:cache:')
    {
        $this->client = $client;
        $this->prefix = $prefix;
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
     * @return PromiseInterface
     */
    public function set($key, $value)
    {
        return $this->client->set($this->prefix . $key, $value);
    }

    /**
     * @param string $key
     * @return PromiseInterface
     */
    public function remove($key)
    {
        return $this->client->del($this->prefix . $key);
    }
}
