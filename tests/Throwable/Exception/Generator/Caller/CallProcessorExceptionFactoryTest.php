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
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Caller\CallProcessorExceptionFactory
 */
class CallProcessorExceptionFactoryTest extends TestCase
{
    public function testCreateForNoProcessorFoundForMethodCall()
    {
        $exception = CallProcessorExceptionFactory::createForNoProcessorFoundForMethodCall(new DummyMethodCall('dummy'));

        $this->assertEquals(
            'No suitable processor found to handle the method call "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
