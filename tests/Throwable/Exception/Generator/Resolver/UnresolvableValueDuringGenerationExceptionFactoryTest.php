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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

use Nelmio\Alice\Throwable\Exception\RootResolutionException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationExceptionFactory
 * @internal
 */
final class UnresolvableValueDuringGenerationExceptionFactoryTest extends TestCase
{
    public function testCreateFromResolutionThrowable(): void
    {
        $previous = new RootResolutionException('Could not find the fixture "some_fixture".');

        $exception = UnresolvableValueDuringGenerationExceptionFactory::createFromResolutionThrowable($previous);

        self::assertEquals(
            'Could not resolve value during the generation process: Could not find the fixture "some_fixture".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
