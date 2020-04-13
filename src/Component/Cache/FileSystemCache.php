<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace GrizzIt\Cache\Component\Cache;

use GrizzIt\Cache\Common\CacheInterface;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Storage\Common\StorageInterface;
use GrizzIt\Storage\Component\ObjectStorage;
use GrizzIt\Cache\Exception\CacheMissException;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;

class FileSystemCache implements CacheInterface
{
    /**
     * Contains the file system where the cache is written to.
     *
     * @var FileSystemInterface
     */
    private $fileSystem;

    /**
     * Contains the file system normalizer.
     *
     * @var FileSystemNormalizerInterface
     */
    private $fileSystemNormalizer;

    /**
     * Contains all data that has been fetched.
     *
     * @var array
     */
    private $cache = [];

    /**
     * Determines whether the buffer is enabled or not.
     *
     * @var bool
     */
    private $bufferEnabled = false;

    /**
     * Stores the keys which need to be rewritten.
     *
     * @var array
     */
    private $rewrite = [];

    /**
     * Constructor.
     *
     * @param FileSystemInterface $fileSystem
     * @param FileSystemNormalizerInterface $fileSystemNormalizer
     */
    public function __construct(
        FileSystemInterface $fileSystem,
        FileSystemNormalizerInterface $fileSystemNormalizer
    ) {
        $this->fileSystem = $fileSystem;
        $this->fileSystemNormalizer = $fileSystemNormalizer;
    }

    /**
     * Retrieves an item from the cache.
     * If it doesn't exist, invokes the $generator and stores the value.
     *
     * @param string $key
     * @param callable $generator
     * @param int $ttl
     *
     * @return StorageInterface
     */
    public function entry(
        string $key,
        callable $generator,
        int $ttl = null
    ): StorageInterface {
        if (!$this->exists($key)) {
            $result = $generator();
            if (is_array($result)) {
                $result = new ObjectStorage($result);
            }

            $this->store($key, $result, $ttl);
        }

        return $this->fetch($key);
    }

    /**
     * Stores a value in the cache.
     *
     * @param string $key
     * @param StorageInterface $value
     * @param int $ttl
     *
     * @return void
     */
    public function store(
        string $key,
        StorageInterface $value,
        int $ttl = null
    ): void {
        $this->data[$key] = $value;

        if (!$this->bufferEnabled) {
            $this->fileSystemNormalizer->denormalizeToFile(
                $this->fileSystem,
                sprintf('%s.json', $key),
                ['ttl' => $ttl, 'value' => iterator_to_array($value)]
            );

            return;
        }

        $this->rewrite[] = ['key' => $key, 'ttl' => $ttl];
    }

    /**
     * Retrieves a value from the cache.
     *
     * @param string $key
     *
     * @return StorageInterface
     *
     * @throws CacheMissException When the value is not available.
     */
    public function fetch(string $key): StorageInterface
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        if ($this->fileSystem->isFile(sprintf('%s.json', $key))) {
            $value = (array) $this->fileSystemNormalizer->normalizeFromFile(
                $this->fileSystem,
                sprintf('%s.json', $key)
            );

            if ($value['ttl'] !== null
            && $value['ttl'] < strtotime('now')) {
                $this->delete($key);

                throw new CacheMissException($key, 'TTL expired');
            }

            $storage = new ObjectStorage((array) $value['value']);
            $this->data[$key] = $storage;

            return $this->data[$key];
        }

        throw new CacheMissException($key, 'Not found');
    }

    /**
     * Checks whether the entry exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        try {
            $this->fetch($key);

            return true;
        } catch (CacheMissException $exception) {
            return false;
        }
    }

    /**
     * Deletes an entry from the cache.
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        $fileName = sprintf('%s.json', $key);
        if ($this->fileSystem->isFile($fileName)) {
            $this->fileSystem->unlink($fileName);
        }
    }

    /**
     * Retrieves an array of keys that exist in the cache.
     *
     * @return array
     */
    public function keys(): array
    {
        $keys = [];
        foreach ($this->fileSystem->list('/') as $name) {
            $key = $this->fileSystem->getPathInfo($name)['filename'];

            if ($this->exists($key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Clears the entire cache.
     *
     * @return void
     */
    public function clear(): void
    {
        foreach ($this->keys() as $key) {
            $this->delete($key);
        }
    }

    /**
     * Enables buffering, this disabled the automatic writing to the cache.
     * The method commit needs to be invoked to perform the write.
     *
     * @return void
     */
    public function enableBuffer(): void
    {
        $this->rewrite = [];
        $this->bufferEnabled = true;
    }

    /**
     * Commits the buffer to the cache.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->bufferEnabled = false;
        foreach ($this->rewrite as $key) {
            if (isset($this->data[$key['key']])) {
                $this->store(
                    $key['key'],
                    $this->data[$key['key']],
                    $key['ttl']
                );
            }
        }
    }
}
