<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace GrizzIt\Cache\Component\Registry;

use GrizzIt\Cache\Common\CacheInterface;
use GrizzIt\Cache\Common\CacheRegistryInterface;

class CacheRegistry implements CacheRegistryInterface
{
    /**
     * Contains all registered caches.
     *
     * @var CacheInterface[]
     */
    private $caches = [];

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
    ): void {
        $this->caches[$key] = $cache;
    }

    /**
     * Retrieves a cache by its key.
     *
     * @param string $key
     *
     * @return CacheInterface
     */
    public function retrieveCache(
        string $key
    ): CacheInterface {
        return $this->caches[$key];
    }

    /**
     * Invokes the clear method on all caches.
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        foreach ($this->caches as $cache) {
            $cache->clear();
        }
    }
}
