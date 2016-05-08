<?php

namespace WyriHaximus\Tests\React\Cache;

use Phake;
use Clue\React\Redis\Client;
use React\Promise\PromiseInterface;
use WyriHaximus\React\Cache\Redis;

class RedisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = Phake::mock(Client::class);
    }

    public function testGet()
    {
        $prefix = 'root:';
        $key = 'key';
        $promise = Phake::mock(PromiseInterface::class);
        Phake::when($this->client)->get($prefix . $key)->thenReturn($promise);
        $result = (new Redis($this->client, $prefix))->get($key);
        $this->assertInstanceOf(PromiseInterface::class, $result);
        $this->assertSame($promise, $result);
        Phake::verify($this->client)->get($prefix . $key);
    }

    public function testSet()
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        (new Redis($this->client, $prefix))->set($key, $value);
        Phake::verify($this->client)->set($prefix . $key, $value);
    }

    public function testRemove()
    {
        $prefix = 'root:';
        $key = 'key';
        (new Redis($this->client, $prefix))->remove($key);
        Phake::verify($this->client)->del($prefix . $key);
    }
}
