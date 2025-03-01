<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use Exception;
use Mockery;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Cache\Redis;

use function React\Async\await;
use function React\Promise\reject;
use function React\Promise\resolve;

final class RedisTest extends AsyncTestCase
{
    public function testGet(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(1));
        $client->expects('get')->with($prefix . $key)->once()->andReturn(resolve($value));
        $promise = (new Redis($client, $prefix))->get($key);

        self::assertSame($value, await($promise));
    }

    public function testGetNonExistant(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(0));
        $client->expects('get')->with($prefix . $key)->once()->andReturn(resolve(null));
        self::assertNull(await((new Redis($client, $prefix))->get($key)));
    }

    public function testSet(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('set')->with($prefix . $key, $value)->once()->andReturn(resolve('OK'));
        (new Redis($client, $prefix))->set($key, $value);
    }

    public function testSetGlobalTtl(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(resolve(null));
        self::assertTrue(await((new Redis($client, $prefix, $ttl))->set($key, $value)));
    }

    public function testSetTtl(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(resolve(null));
        self::assertTrue(await((new Redis($client, $prefix))->set($key, $value, $ttl)));
    }

    public function testSetTtlException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(reject(new Exception('fail!')));
        self::assertFalse(await((new Redis($client, $prefix))->set($key, $value, $ttl)));
    }

    public function testSetException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('set')->with($prefix . $key, $value)->once()->andReturn(reject(new Exception('fail!')));
        self::assertFalse(await((new Redis($client, $prefix))->set($key, $value)));
    }

    public function testDelete(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(resolve(1));
        (new Redis($client, $prefix))->delete($key);
    }

    public function testDeleteException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(reject(new Exception('fail!')));
        (new Redis($client, $prefix))->delete($key);
    }

    public function testHas(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(1));
        (new Redis($client, $prefix))->has($key);
    }

    public function testDeleteMultiple(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(resolve(1));
        (new Redis($client, $prefix))->deleteMultiple([$key]);
    }

    public function testDeleteMultipleException(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('del')->with($prefix . $key)->once()->andReturn(reject(new Exception('fail!')));
        (new Redis($client, $prefix))->deleteMultiple([$key]);
    }

    public function testCLear(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $client->expects('keys')->with($prefix . '*')->once()->andReturn(resolve([$key]));
        $client->expects('del')->with($prefix . $key)->once()->andReturn(resolve(1));
        (new Redis($client, $prefix))->clear();
    }

    public function testSetMultiple(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $ttl    = 123;
        $client->expects('psetex')->with($prefix . $key, $ttl * 1000, $value)->once()->andReturn(resolve(true));
        self::assertSame(true, await((new Redis($client, $prefix))->setMultiple([$key => $value], $ttl))); /** @phpstan-ignore-line */
    }

    public function testGetMultiple(): void
    {
        $client = Mockery::mock(ClientStub::class);
        $prefix = 'root:';
        $key    = 'key';
        $value  = 'value';
        $client->expects('exists')->with($prefix . $key)->once()->andReturn(resolve(true));
        $client->expects('get')->with($prefix . $key)->once()->andReturn(resolve($value));
        self::assertSame([$key => $value], await((new Redis($client, $prefix))->getMultiple([$key])));
    }
}
