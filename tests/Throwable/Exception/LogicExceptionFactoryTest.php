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

namespace Nelmio\Alice\Throwable\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\LogicExceptionFactory
 */
class LogicExceptionFactoryTest extends TestCase
{
    public function testCreateForUncallableMethod()
    {
        $exception = LogicExceptionFactory::createForUncallableMethod('foo');

        $this->assertEquals(
            'By its nature, "foo()" should not be called.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForCannotDenormalizerForChainableFixtureBuilderDenormalizer()
    {
        $exception = LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer('foo');

        $this->assertEquals(
            'As a chainable denormalizer, "foo" should be called only if "::canDenormalize() returns true. Got '
            .'false instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForCannotHaveBothConstructorAndFactory()
    {
        $exception = LogicExceptionFactory::createForCannotHaveBothConstructorAndFactory();

        $this->assertEquals(
            'Cannot use the fixture property "__construct" and "__factory" together.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
