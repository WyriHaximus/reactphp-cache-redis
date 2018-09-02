<?php declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use ApiClients\Tools\TestUtilities\TestCase;
use Clue\React\Redis\Client;
use Phake;
use React\EventLoop\Factory;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use WyriHaximus\React\Cache\Redis;
use function Clue\React\Block\await;

final class RedisTest extends TestCase
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
        $value = 'value';
        Phake::when($this->client)->exists($prefix . $key)->thenReturn(new FulfilledPromise(1));
        Phake::when($this->client)->get($prefix . $key)->thenReturn(new FulfilledPromise($value));
        $promise = (new Redis($this->client, $prefix))->get($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $result = await($promise, Factory::create());
        $this->assertSame($value, $result);
        Phake::inOrder(
            Phake::verify($this->client)->exists($prefix . $key),
            Phake::verify($this->client)->get($prefix . $key)
        );
    }

    public function testGetNonExistant()
    {
        $prefix = 'root:';
        $key = 'key';
        Phake::when($this->client)->exists($prefix . $key)->thenReturn(new FulfilledPromise(0));
        Phake::when($this->client)->get($prefix . $key)->thenReturn(new RejectedPromise());
        $promise = (new Redis($this->client, $prefix))->get($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertInstanceOf(FulfilledPromise::class, $promise);
        Phake::verify($this->client)->exists($prefix . $key);
        Phake::verify($this->client, Phake::never())->get($prefix . $key);
    }

    public function testSet()
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        Phake::when($this->client)->set($prefix . $key, $value)->thenReturn(new FulfilledPromise('OK'));
        $promise = (new Redis($this->client, $prefix))->set($key, $value);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        Phake::verify($this->client)->set($prefix . $key, $value);
    }

    public function testSetGlobalTtl()
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        Phake::when($this->client)->set($prefix . $key, $value)->thenReturn(new FulfilledPromise());
        (new Redis($this->client, $prefix, $ttl))->set($key, $value);
        Phake::verify($this->client)->set($prefix . $key, $value);
        Phake::verify($this->client)->expire($prefix . $key, $ttl);
    }

    public function testSetTtl()
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        Phake::when($this->client)->set($prefix . $key, $value)->thenReturn(new FulfilledPromise());
        (new Redis($this->client, $prefix))->set($key, $value, $ttl);
        Phake::verify($this->client)->set($prefix . $key, $value);
        Phake::verify($this->client)->expire($prefix . $key, $ttl);
    }

    public function testDelete()
    {
        $prefix = 'root:';
        $key = 'key';
        Phake::when($this->client)->del($prefix . $key)->thenReturn(new FulfilledPromise(1));
        $promise = (new Redis($this->client, $prefix))->delete($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        Phake::verify($this->client)->del($prefix . $key);
    }
}
