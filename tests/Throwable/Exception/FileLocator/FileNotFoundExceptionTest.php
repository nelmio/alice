<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Throwable\Exception\FileLocator;

use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException
 */
class FileNotFoundExceptionTest extends TestCase
{
    public function testIsAnUnexpectedValueException(): void
    {
        static::assertTrue(is_a(FileNotFoundException::class, UnexpectedValueException::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildFileNotFoundException();
        static::assertInstanceOf(ChildFileNotFoundException::class, $exception);
    }

    public function testCreateForEmptyFile(): void
    {
        $exception = FileNotFoundException::createForEmptyFile();

        static::assertEquals(
            'An empty file name is not valid to be located.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForNonExistentFile(): void
    {
        $exception = FileNotFoundException::createForNonExistentFile('foo.yml');

        static::assertEquals(
            'The file "foo.yml" does not exist.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
