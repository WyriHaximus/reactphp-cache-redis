<?php declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use ApiClients\Tools\TestUtilities\TestCase;
use Clue\React\Redis\Client;
use React\EventLoop\Factory;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use WyriHaximus\React\Cache\Redis;
use function Clue\React\Block\await;

/**
 * @internal
 */
final class RedisTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->prophesize(ClientStub::class);
    }

    public function testGet(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $this->client->exists($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(1));
        $this->client->get($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise($value));
        $promise = (new Redis($this->client->reveal(), $prefix))->get($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $result = $this->await($promise, Factory::create());
        $this->assertSame($value, $result);
    }

    public function testGetNonExistant(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->exists($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(0));
        $this->client->get($prefix . $key)->shouldNotBeCalled()->willReturn(new RejectedPromise());
        $promise = (new Redis($this->client->reveal(), $prefix))->get($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertInstanceOf(FulfilledPromise::class, $promise);
    }

    public function testSet(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $this->client->set($prefix . $key, $value)->shouldBeCalled()->willReturn(new FulfilledPromise('OK'));
        $promise = (new Redis($this->client->reveal(), $prefix))->set($key, $value);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testSetGlobalTtl(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        $this->client->set($prefix . $key, $value)->shouldBeCalled()->willReturn(new FulfilledPromise());
        $this->client->expire($prefix . $key, $ttl)->shouldBeCalled()->willReturn(new FulfilledPromise());
        (new Redis($this->client->reveal(), $prefix, $ttl))->set($key, $value);
    }

    public function testSetTtl(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        $this->client->set($prefix . $key, $value)->shouldBeCalled()->willReturn(new FulfilledPromise());
        $this->client->expire($prefix . $key, $ttl)->shouldBeCalled()->willReturn(new FulfilledPromise());
        (new Redis($this->client->reveal(), $prefix))->set($key, $value, $ttl);
    }

    public function testDelete(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->del($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(1));
        $promise = (new Redis($this->client->reveal(), $prefix))->delete($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }
}
