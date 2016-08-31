<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException
 */
class ResolverNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(ResolverNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAResolutionThrowable()
    {
        $this->assertFalse(is_a(ResolverNotFoundException::class, ResolutionThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactoryForParameter()
    {
        $exception = ResolverNotFoundException::createForParameter('foo');

        $this->assertEquals(
            'No resolver found to resolve parameter "foo".',
            $exception->getMessage()
        );
    }

    public function testTestCreateNewExceptionWithFactoryForValue()
    {
        $exception = ResolverNotFoundException::createForValue(new DummyValue('dummy'));

        $this->assertEquals(
            'No resolver found to resolve value "dummy".',
            $exception->getMessage()
        );
    }

    public function testTestCreateNewExceptionWithFactoryForUnexpectedCall()
    {
        $exception = ResolverNotFoundException::createUnexpectedCall('fake');

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a resolver.',
            $exception->getMessage()
        );
    }
}
