# Redis cache implementation for react/cache

[![Build Status](https://travis-ci.com/WyriHaximus/reactphp-cache-redis.svg?branch=master)](https://travis-ci.com/WyriHaximus/reactphp-cache-redis)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/react-cache-redis/v/stable.png)](https://packagist.org/packages/WyriHaximus/react-cache-redis)
[![Total Downloads](https://poser.pugx.org/WyriHaximus/react-cache-redis/downloads.png)](https://packagist.org/packages/WyriHaximus/react-cache-redis)
[![Code Coverage](https://scrutinizer-ci.com/g/WyriHaximus/reactphp-cache-redis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WyriHaximus/reactphp-cache-redis/?branch=master)
[![License](https://poser.pugx.org/WyriHaximus/react-cache-redis/license.png)](https://packagist.org/packages/WyriHaximus/react-cache-redis)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/reactphp-cache-redis/badge.svg)](https://travis-ci.org/WyriHaximus/reactphp-cache-redis)

Use redis as a cache, implementing the [react/cache interface](https://github.com/reactphp/cache)

# Installation

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/react-cache-redis 
```

# Usage

```php
<?php

use Clue\React\Redis\Client;
use Clue\React\Redis\Factory as RedisFactory;
use React\EventLoop\Factory as LoopFactory;
use WyriHaximus\React\Cache\Redis;

$loop = LoopFactory::create();
$factory = new RedisFactory($loop);

$factory->createClient()->then(function (Client $client) {
    $cache = new Redis($client, 'react:cache:your:key:prefix:');
});
```

# License

The MIT License (MIT)

Copyright (c) 2018 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
