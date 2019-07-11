<?php declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use React\EventLoop\Factory;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use function React\Promise\reject;
use React\Promise\RejectedPromise;
use function React\Promise\resolve;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Cache\Redis;

/**
 * @internal
 */
final class RedisTest extends AsyncTestCase
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
        $this->client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(new FulfilledPromise());
        self::assertTrue(($this->await((new Redis($this->client->reveal(), $prefix, $ttl))->set($key, $value))));
    }

    public function testSetTtl(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        $this->client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(new FulfilledPromise());
        self::assertTrue(($this->await((new Redis($this->client->reveal(), $prefix))->set($key, $value, $ttl))));
    }

    public function testSetTtlException(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        $this->client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(reject(new \Exception('fail!')));
        self::assertFalse(($this->await((new Redis($this->client->reveal(), $prefix))->set($key, $value, $ttl))));
    }

    public function testSetException(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $this->client->set($prefix . $key, $value)->shouldBeCalled()->willReturn(reject(new \Exception('fail!')));
        self::assertFalse(($this->await((new Redis($this->client->reveal(), $prefix))->set($key, $value))));
    }

    public function testDelete(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->del($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(1));
        $promise = (new Redis($this->client->reveal(), $prefix))->delete($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testDeleteException(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->del($prefix . $key)->shouldBeCalled()->willReturn(reject(new \Exception('fail!')));
        $promise = (new Redis($this->client->reveal(), $prefix))->delete($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testHas(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->exists($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(1));
        $promise = (new Redis($this->client->reveal(), $prefix))->has($key);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testDeleteMultiple(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->del($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(1));
        $promise = (new Redis($this->client->reveal(), $prefix))->deleteMultiple([$key]);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testDeleteMultipleException(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->del($prefix . $key)->shouldBeCalled()->willReturn(reject(new \Exception('fail!')));
        $promise = (new Redis($this->client->reveal(), $prefix))->deleteMultiple([$key]);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testCLear(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $this->client->keys($prefix . '*')->shouldBeCalled()->willReturn(resolve([$key]));
        $this->client->del($prefix . $key)->shouldBeCalled()->willReturn(new FulfilledPromise(1));
        $promise = (new Redis($this->client->reveal(), $prefix))->clear();
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testSetMultiple(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $ttl = 123;
        $this->client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(resolve(true));
        self::assertSame([$key => true], ($this->await((new Redis($this->client->reveal(), $prefix))->setMultiple([$key => $value], $ttl))));
    }

    public function testGetMultiple(): void
    {
        $prefix = 'root:';
        $key = 'key';
        $value = 'value';
        $this->client->exists($prefix . $key)->shouldBeCalled()->willReturn(resolve(true));
        $this->client->get($prefix . $key)->shouldBeCalled()->willReturn(resolve($value));
        self::assertSame([$key => $value], ($this->await((new Redis($this->client->reveal(), $prefix))->getMultiple([$key]))));
    }
}
