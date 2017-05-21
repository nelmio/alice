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
    public function testIsAnUnexpectedValueException()
    {
        $this->assertTrue(is_a(FileNotFoundException::class, UnexpectedValueException::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildFileNotFoundException();
        $this->assertInstanceOf(ChildFileNotFoundException::class, $exception);
    }

    public function testCreateForEmptyFile()
    {
        $exception = FileNotFoundException::createForEmptyFile();

        $this->assertEquals(
            'An empty file name is not valid to be located.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForNonExistentFile()
    {
        $exception = FileNotFoundException::createForNonExistentFile('foo.yml');

        $this->assertEquals(
            'The file "foo.yml" does not exist.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
