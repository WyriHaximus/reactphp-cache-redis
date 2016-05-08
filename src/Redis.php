<?php

namespace WyriHaximus\React\Cache;

use Clue\React\Redis\Client;
use React\Cache\CacheInterface;
use React\Promise\PromiseInterface;

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
        return $this->client->get($this->prefix . $key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->client->set($this->prefix . $key, $value);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        $this->client->del($this->prefix . $key);
    }
}
