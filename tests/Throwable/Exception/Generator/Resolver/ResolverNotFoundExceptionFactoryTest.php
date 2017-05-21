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

use Nelmio\Alice\Definition\Value\DummyValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory
 */
class ResolverNotFoundExceptionFactoryTest extends TestCase
{
    public function testCreateNewExceptionWithFactoryForParameter()
    {
        $exception = ResolverNotFoundExceptionFactory::createForParameter('foo');

        $this->assertEquals(
            'No resolver found to resolve parameter "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateNewExceptionWithFactoryForValue()
    {
        $exception = ResolverNotFoundExceptionFactory::createForValue(new DummyValue('dummy'));

        $this->assertEquals(
            'No resolver found to resolve value "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateNewExceptionWithFactoryForUnexpectedCall()
    {
        $exception = ResolverNotFoundExceptionFactory::createUnexpectedCall('fake');

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a resolver.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
