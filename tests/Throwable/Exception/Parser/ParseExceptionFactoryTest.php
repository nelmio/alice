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

namespace Nelmio\Alice\Throwable\Exception\Parser;

use Error;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Parser\ParseExceptionFactory
 */
class ParseExceptionFactoryTest extends TestCase
{
    public function testCreateForParserNoFoundForFile(): void
    {
        $exception = ParseExceptionFactory::createForParserNoFoundForFile('foo');

        static::assertEquals(
            'No suitable parser found for the file "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableFile(): void
    {
        $exception = ParseExceptionFactory::createForUnparsableFile('foo');

        static::assertEquals(
            'Could not parse the file "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = ParseExceptionFactory::createForUnparsableFile('foo', $code, $previous);

        static::assertEquals(
            'Could not parse the file "foo".',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidYaml(): void
    {
        $exception = ParseExceptionFactory::createForInvalidYaml('foo');

        static::assertEquals(
            'The file "foo" does not contain valid YAML.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = ParseExceptionFactory::createForInvalidYaml('foo', $code, $previous);

        static::assertEquals(
            'The file "foo" does not contain valid YAML.',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }
}
