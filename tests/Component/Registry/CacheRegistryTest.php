<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace GrizzIt\Cache\Tests\Component\Registry;

use PHPUnit\Framework\TestCase;
use GrizzIt\Cache\Common\CacheInterface;
use GrizzIt\Cache\Component\Registry\CacheRegistry;

/**
 * @coversDefaultClass \GrizzIt\Cache\Component\Registry\CacheRegistry
 */
class CacheRegistryTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::registerCache
     * @covers ::retrieveCache
     * @covers ::clearAllCaches
     */
    public function testRegistry(): void
    {
        $subject = new CacheRegistry();

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects(static::once())
            ->method('clear');

        $subject->registerCache('test', $cache);

        $this->assertEquals($cache, $subject->retrieveCache('test'));

        $subject->clearAllCaches();
    }
}
