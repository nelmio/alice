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

namespace Nelmio\Alice\Throwable\Exception\Generator\Caller;

use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(CallProcessorExceptionFactory::class)]
final class CallProcessorExceptionFactoryTest extends TestCase
{
    public function testCreateForNoProcessorFoundForMethodCall(): void
    {
        $exception = CallProcessorExceptionFactory::createForNoProcessorFoundForMethodCall(new DummyMethodCall('dummy'));

        self::assertEquals(
            'No suitable processor found to handle the method call "dummy".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
