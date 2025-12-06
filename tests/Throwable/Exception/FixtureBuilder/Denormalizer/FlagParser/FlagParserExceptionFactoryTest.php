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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory
 * @internal
 */
final class FlagParserExceptionFactoryTest extends TestCase
{
    public function testCreateNewException(): void
    {
        $exception = FlagParserExceptionFactory::createForNoParserFoundForElement('foo');

        self::assertEquals(
            'No suitable flag parser found to handle the element "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateNewExceptionForUnexpectedCall(): void
    {
        $exception = FlagParserExceptionFactory::createForExpectedMethodToBeCalledIfHasAParser('foo');

        self::assertEquals(
            'Expected method "foo" to be called only if it has a flag parser.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
