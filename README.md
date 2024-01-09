# Redis cache implementation for react/cache

![Continuous Integration](https://github.com/wyrihaximus/reactphp-cache-redis/workflows/Continuous%20Integration/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/wyrihaximus/react-cache-redis/v/stable.png)](https://packagist.org/packages/wyrihaximus/react-cache-redis)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/react-cache-redis/downloads.png)](https://packagist.org/packages/wyrihaximus/react-cache-redis/stats)
[![Type Coverage](https://shepherd.dev/github/WyriHaximus/reactphp-cache-redis/coverage.svg)](https://shepherd.dev/github/WyriHaximus/reactphp-cache-redis)
[![License](https://poser.pugx.org/wyrihaximus/react-cache-redis/license.png)](https://packagist.org/packages/wyrihaximus/react-cache-redis)

Use Redis as a cache, implementing the [react/cache interface](https://github.com/reactphp/cache)

# Installation

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/react-cache-redis 
```

# Usage

```php
<?php

use Clue\React\Redis\Client;
use Clue\React\Redis\Factory;
use WyriHaximus\React\Cache\Redis;

(new Factory())->createClient()->then(function (Client $client) {
    $cache = new Redis($client, 'react:cache:your:key:prefix:');
});
```

# License

The MIT License (MIT)

Copyright (c) 2024 Cees-Jan Kiewiet

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
