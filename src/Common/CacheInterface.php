<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace GrizzIt\Cache\Common;

use GrizzIt\Storage\Common\StorageInterface;

interface CacheInterface
{
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
    ): StorageInterface;

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
    ): void;

    /**
     * Retrieves a value from the cache.
     *
     * @param string $key
     *
     * @return StorageInterface
     */
    public function fetch(string $key): StorageInterface;

    /**
     * Checks whether the entry exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Deletes an entry from the cache.
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Retrieves an array of keys that exist in the cache.
     *
     * @return array
     */
    public function keys(): array;

    /**
     * Clears the entire cache.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Enables buffering, this disabled the automatic writing to the cache.
     * The method commit needs to be invoked to perform the write.
     *
     * @return void
     */
    public function enableBuffer(): void;

    /**
     * Commits the buffer to the cache.
     *
     * @return void
     */
    public function commit(): void;
}
