<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Cache\Redis;

use function React\Async\await;
use function React\Promise\reject;
use function React\Promise\resolve;

final class RedisTest extends AsyncTestCase
{
    #[Test]
    public function get(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(1));
        $client->expects('get')->with($prefix . $key)->once()->andReturn(resolve($value));
        $promise = new Redis($client, $prefix)->get($key);

        self::assertSame($value, await($promise));
    }

    #[Test]
    public function getNonExistant(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(0));
        $client->expects('get')->with($prefix . $key)->once()->andReturn(resolve(null));
        self::assertNull(await(new Redis($client, $prefix)->get($key)));
    }

    #[Test]
    public function set(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('set')->with($prefix . $key, $value)->once()->andReturn(resolve('OK'));
        new Redis($client, $prefix)->set($key, $value);
    }

    #[Test]
    public function setGlobalTtl(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(resolve(null));
        self::assertTrue(await(new Redis($client, $prefix, $ttl)->set($key, $value)));
    }

    #[Test]
    public function setTtl(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(resolve(null));
        self::assertTrue(await(new Redis($client, $prefix)->set($key, $value, $ttl)));
    }

    #[Test]
    public function setTtlException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(reject(new Exception('fail!')));
        self::assertFalse(await(new Redis($client, $prefix)->set($key, $value, $ttl)));
    }

    #[Test]
    public function setException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('set')->with($prefix . $key, $value)->once()->andReturn(reject(new Exception('fail!')));
        self::assertFalse(await(new Redis($client, $prefix)->set($key, $value)));
    }

    #[Test]
    public function delete(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(resolve(1));
        new Redis($client, $prefix)->delete($key);
    }

    #[Test]
    public function deleteException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(reject(new Exception('fail!')));
        new Redis($client, $prefix)->delete($key);
    }

    #[Test]
    public function has(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(1));
        new Redis($client, $prefix)->has($key);
    }

    #[Test]
    public function deleteMultiple(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(resolve(1));
        new Redis($client, $prefix)->deleteMultiple([$key]);
    }

    #[Test]
    public function deleteMultipleException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(reject(new Exception('fail!')));
        new Redis($client, $prefix)->deleteMultiple([$key]);
    }

    #[Test]
    public function cLear(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('keys')->with($prefix . '*')->once()->andReturn(resolve([$key]));
        $client->expects('del')->with($prefix . $key)->once()->andReturn(resolve(1));
        new Redis($client, $prefix)->clear();
    }

    #[Test]
    public function setMultiple(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(resolve(true));
        self::assertSame(true, await(new Redis($client, $prefix)->setMultiple([$key => $value], $ttl)));
    }

    #[Test]
    public function getMultiple(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(true));
        $client->expects('get')->with($prefix . $key)->once()->andReturn(resolve($value));
        self::assertSame([$key => $value], await(new Redis($client, $prefix)->getMultiple([$key])));
    }
}
