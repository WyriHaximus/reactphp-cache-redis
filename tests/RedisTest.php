<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use Exception;
use React\EventLoop\Factory;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Cache\Redis;

use function React\Promise\reject;
use function React\Promise\resolve;

/**
 * @internal
 */
final class RedisTest extends AsyncTestCase
{
    public function testGet(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->exists($prefix . $key)->shouldBeCalled()->willReturn(resolve(1));
        $client->get($prefix . $key)->shouldBeCalled()->willReturn(resolve($value));
        $promise = (new Redis($client->reveal(), $prefix))->get($key);

        $result = $this->await($promise, Factory::create());
        self::assertSame($value, $result);
    }

    public function testGetNonExistant(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->exists($prefix . $key)->shouldBeCalled()->willReturn(resolve(0));
        $client->get($prefix . $key)->shouldBeCalled()->willReturn(resolve(null));
        self::assertNull($this->await((new Redis($client->reveal(), $prefix))->get($key)));
    }

    public function testSet(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->set($prefix . $key, $value)->shouldBeCalled()->willReturn(resolve('OK'));
        (new Redis($client->reveal(), $prefix))->set($key, $value);
    }

    public function testSetGlobalTtl(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(resolve());
        self::assertTrue($this->await((new Redis($client->reveal(), $prefix, $ttl))->set($key, $value)));
    }

    public function testSetTtl(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(resolve());
        self::assertTrue($this->await((new Redis($client->reveal(), $prefix))->set($key, $value, $ttl)));
    }

    public function testSetTtlException(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(reject(new Exception('fail!')));
        self::assertFalse($this->await((new Redis($client->reveal(), $prefix))->set($key, $value, $ttl)));
    }

    public function testSetException(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->set($prefix . $key, $value)->shouldBeCalled()->willReturn(reject(new Exception('fail!')));
        self::assertFalse($this->await((new Redis($client->reveal(), $prefix))->set($key, $value)));
    }

    public function testDelete(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->del($prefix . $key)->shouldBeCalled()->willReturn(resolve(1));
        (new Redis($client->reveal(), $prefix))->delete($key);
    }

    public function testDeleteException(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->del($prefix . $key)->shouldBeCalled()->willReturn(reject(new Exception('fail!')));
        (new Redis($client->reveal(), $prefix))->delete($key);
    }

    public function testHas(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->exists($prefix . $key)->shouldBeCalled()->willReturn(resolve(1));
        (new Redis($client->reveal(), $prefix))->has($key);
    }

    public function testDeleteMultiple(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->del($prefix . $key)->shouldBeCalled()->willReturn(resolve(1));
        (new Redis($client->reveal(), $prefix))->deleteMultiple([$key]);
    }

    public function testDeleteMultipleException(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->del($prefix . $key)->shouldBeCalled()->willReturn(reject(new Exception('fail!')));
        (new Redis($client->reveal(), $prefix))->deleteMultiple([$key]);
    }

    public function testCLear(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->keys($prefix . '*')->shouldBeCalled()->willReturn(resolve([$key]));
        $client->del($prefix . $key)->shouldBeCalled()->willReturn(resolve(1));
        (new Redis($client->reveal(), $prefix))->clear();
    }

    public function testSetMultiple(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->psetex($prefix . $key, $ttl * 1000, $value)->shouldBeCalled()->willReturn(resolve(true));
        self::assertSame([$key => true], $this->await((new Redis($client->reveal(), $prefix))->setMultiple([$key => $value], $ttl)));
    }

    public function testGetMultiple(): void
    {
        $client = $this->prophesize(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->exists($prefix . $key)->shouldBeCalled()->willReturn(resolve(true));
        $client->get($prefix . $key)->shouldBeCalled()->willReturn(resolve($value));
        self::assertSame([$key => $value], $this->await((new Redis($client->reveal(), $prefix))->getMultiple([$key])));
    }
}
