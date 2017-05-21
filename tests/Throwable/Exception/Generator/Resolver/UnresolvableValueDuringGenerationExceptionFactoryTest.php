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
 */
class UnresolvableValueDuringGenerationExceptionFactoryTest extends TestCase
{
    public function testCreateFromResolutionThrowable()
    {
        $previous = new RootResolutionException();

        $exception = UnresolvableValueDuringGenerationExceptionFactory::createFromResolutionThrowable($previous);

        $this->assertEquals(
            'Could not resolve value during the generation process.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
