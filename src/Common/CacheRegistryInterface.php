<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace GrizzIt\Cache\Common;

interface CacheRegistryInterface
{
    /**
     * Registers a cache by a key.
     *
     * @param string $key
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function registerCache(
        string $key,
        CacheInterface $cache
    ): void;

    /**
     * Retrieves a cache by its key.
     *
     * @param string $key
     * @return CacheInterface
     */
    public function retrieveCache(
        string $key
    ): CacheInterface;

    /**
     * Invokes the clear method on all caches.
     *
     * @return void
     */
    public function clearAllCaches(): void;
}
