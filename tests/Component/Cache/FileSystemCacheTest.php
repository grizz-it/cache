<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace GrizzIt\Cache\Tests\Component\Cache;

use PHPUnit\Framework\TestCase;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Storage\Component\ObjectStorage;
use GrizzIt\Cache\Exception\CacheMissException;
use GrizzIt\Cache\Component\Cache\FileSystemCache;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;

/**
 * @coversDefaultClass \GrizzIt\Cache\Component\Cache\FileSystemCache
 * @covers \GrizzIt\Cache\Exception\CacheMissException
 */
class FileSystemCacheTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::entry
     * @covers ::exists
     * @covers ::fetch
     * @covers ::store
     */
    public function testEntry(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(
            FileSystemNormalizerInterface::class
        );

        $subject = new FileSystemCache($fileSystem, $fileSystemNormalizer);

        $callable = ( function () {
            return ['bar'];
        });

        $time = strtotime('+10 minutes');

        $fileSystem->expects(static::once())
            ->method('isFile')
            ->with('foo.json')
            ->willReturn(false);

        $result = $subject->entry(
            'foo',
            $callable,
            $time
        );

        $this->assertEquals(
            ['bar'],
            iterator_to_array($result)
        );

        $this->assertSame($result, $subject->entry(
            'foo',
            $callable,
            $time
        ));
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::store
     * @covers ::fetch
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(
            FileSystemNormalizerInterface::class
        );

        $subject = new FileSystemCache($fileSystem, $fileSystemNormalizer);

        $input = new ObjectStorage(['bar']);

        $subject->store('foo', $input);

        $this->assertSame($input, $subject->fetch('foo'));

        $subject->delete('foo');

        $this->expectException(CacheMissException::class);
        $subject->fetch('foo');
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::keys
     * @covers ::exists
     * @covers ::fetch
     * @covers ::clear
     */
    public function testClear(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(
            FileSystemNormalizerInterface::class
        );

        $subject = new FileSystemCache($fileSystem, $fileSystemNormalizer);

        $fileSystem->expects(static::once())
            ->method('list')
            ->with('/')
            ->willReturn(['foo.json', 'bar.json', 'baz.json']);

        $fileSystem->expects(static::exactly(3))
            ->method('getPathInfo')
            ->withConsecutive(['foo.json'], ['bar.json'], ['baz.json'])
            ->willReturnOnConsecutiveCalls(
                ['filename' => 'foo'],
                ['filename' => 'bar'],
                ['filename' => 'baz']
            );

        $fileSystem->expects(static::exactly(6))
            ->method('isFile')
            ->withConsecutive(['foo.json'], ['bar.json'], ['baz.json'])
            ->willReturn(true);

        $fileSystemNormalizer->expects(static::exactly(3))
            ->method('normalizeFromFile')
            ->withConsecutive(
                [$fileSystem, 'foo.json'],
                [$fileSystem, 'bar.json'],
                [$fileSystem, 'baz.json']
            )->willReturn(json_decode(sprintf(
                '{"value": {"foo": "bar"}, "ttl": %s}',
                strtotime('+10 minutes')
            )));

        $subject->clear();
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::fetch
     * @covers ::delete
     */
    public function testFetchMiss(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(
            FileSystemNormalizerInterface::class
        );

        $subject = new FileSystemCache($fileSystem, $fileSystemNormalizer);

        $fileSystem->expects(static::exactly(2))
            ->method('isFile')
            ->with('foo.json')
            ->willReturn(true);

        $fileSystemNormalizer->expects(static::once())
            ->method('normalizeFromFile')
            ->with($fileSystem, 'foo.json')
            ->willReturn(json_decode(sprintf(
                '{"value": {"foo": "bar"}, "ttl": %s}',
                strtotime('-10 minutes')
            )));

        $this->expectException(CacheMissException::class);

        $subject->fetch('foo');
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::store
     * @covers ::enableBuffer
     * @covers ::commit
     */
    public function testBufferedWrite(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(
            FileSystemNormalizerInterface::class
        );

        $subject = new FileSystemCache($fileSystem, $fileSystemNormalizer);

        $time = strtotime('-10 minutes');

        $fileSystemNormalizer->expects(static::once())
            ->method('denormalizeToFile')
            ->with(
                $fileSystem,
                'foo.json',
                ['ttl' => $time, 'value' => ['foo' => 'bar']]
            );

        $subject->enableBuffer();
        $subject->store('foo', new ObjectStorage(['foo' => 'bar']), $time);
        $subject->commit();
    }
}
