# GrizzIT Cache - Using the cache registry

The cache registry is an object responsible for maintaining all caches in a system.
It can also be used to clear all active caches.

## Usage

Creating a cache registry is simple, it requires not constructor parameters.

```php
<?php

use GrizzIt\Cache\Component\Registry\CacheRegistry;

$registry = new CacheRegistry();
```

To pass a cache to the registry, simple invoke the `registerCache` method with a key
and the instance of the cache.

```php
<?php

$registry->registerCache('foo', $fooCache);
```

Then the application can simply retrieve the same instance by calling `retrieveCache`.

```php
<?php

$registry->retrieveCache('foo');
```

It is also possible to clear all cache that are present in the registry, by simple
invoking the `clearAllCaches` method.

```php
<?php

$registry->clearAllCaches();
```


## Further reading

[Back to usage index](index.md)

[Creating a file system cache](creating-a-file-system-cache.md)
