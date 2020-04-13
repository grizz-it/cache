# GrizzIT Cache - Creating a file system cache

A file system cache is a basic caching mechanism to store information for an
application. An example would be to store all aggregated configuration for an application
in a cache, so it can be fetched from one location.

In order to create a cache, a `FileSystemDriverInterface` needs to be prepared. Please
see the [grizz-it/vfs](https://github.com/grizz-it/vfs) package for more information.
To create an instance, simply pass an active file system and the normalizer.

```php
<?php

use GrizzIt\Cache\Component\Cache\FileSystemCache;
use GrizzIt\Vfs\Common\FileSystemDriverInterface;

/** @var FileSystemDriverInterface $driver */
$cache = new FileSystemCache(
    $driver->connect('cache'),
    $driver->getFileSystemNormalizer()
);
```

## Usage

### Storing data

To store information in the cache there are two ways of doing so. It can be
done through the `store` method, this method will always overwrite what is currently
in the storage. Optionally a TTL can be passed to the store method, this will cause
the contents to be automatically invalidated when current time has passed the set TTL.
An example of using this method would look like the following snippet:
```php
<?php

use GrizzIt\Storage\Component\ObjectStorage;

$cache->store(
    'foo',
    new ObjectStorage(['bar' => 'baz']),
    strtotime('+10 minutes')
);
```

It is also possible to buffer all changes and commit them all in one call. This
can be helpful when a lot of writes are being done to the same storage and only
the final commit should be stored. This would look like the following example:

```php
<?php

use GrizzIt\Storage\Component\ObjectStorage;

$cache->enableBuffer();

$cache->store(
    'foo',
    new ObjectStorage(['bar' => 'baz']),
    strtotime('+10 minutes')
);

$cache->commit();
```

### Clearing the cache
The entire cache can be clear by simply invoking the `clear` method. This will
delete all entries in the cache. It is also possible to only delete a set of entries,
this can be done with the `delete` method by passing the key that should be deleted.

### Fetching data

There are two methods for fetching data from the cache. The method is `fetch`, this
method will return a `StorageInterface` (see
[grizz-it/storage](https://github.com/grizz-it/storage)) when passing a key. However
when the key does not exist, it will throw a `CacheMissException`. If the application
is not responsible for building up the cache, it should invoke the `exists` method prior
to invoking the `fetch` method to prevent/work around this behaviour.

The other possible method is `entry` method. This method accepts a key, but also a
callable and a TTL. These last two variables are meant for generating the data when it
doesn't exist and return the result of that invocation. The result will also be stored
on the key for future reference. The callable can either return an array or an instance
of the `StorageInterface`.

```php
<?php

$resultEntry = $cache->entry(
    'foo',
    ( function () {
        return ['foo' => 'bar'];
    }),
    strtotime('+10 minutes')
);

$resultFetch = $cache->fetch('foo');
```

In the example above the value on `$resultEntry` and `$resultFetch` would be exactly the
same. Because during the `entry` invocation, the value was created and stored.

All current keys in the cache can be retrieved with the `keys` method.

## Further reading

[Back to usage index](index.md)

[Using the cache registry](using-the-cache-registry.md)
