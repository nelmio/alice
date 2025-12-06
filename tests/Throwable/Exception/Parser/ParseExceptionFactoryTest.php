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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ParseExceptionFactory::class)]
final class ParseExceptionFactoryTest extends TestCase
{
    public function testCreateForParserNoFoundForFile(): void
    {
        $exception = ParseExceptionFactory::createForParserNoFoundForFile('foo');

        self::assertEquals(
            'No suitable parser found for the file "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableFile(): void
    {
        $exception = ParseExceptionFactory::createForUnparsableFile('foo');

        self::assertEquals(
            'Could not parse the file "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = ParseExceptionFactory::createForUnparsableFile('foo', $code, $previous);

        self::assertEquals(
            'Could not parse the file "foo".',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidYaml(): void
    {
        $exception = ParseExceptionFactory::createForInvalidYaml('foo');

        self::assertEquals(
            'The file "foo" does not contain valid YAML.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = ParseExceptionFactory::createForInvalidYaml('foo', $code, $previous);

        self::assertEquals(
            'The file "foo" does not contain valid YAML.',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
