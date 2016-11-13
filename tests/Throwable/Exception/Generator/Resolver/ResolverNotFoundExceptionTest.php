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
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
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
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = ResolverNotFoundException::createForParameter('foo', $code, $previous);

        $this->assertEquals(
            'No resolver found to resolve parameter "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateNewExceptionWithFactoryForValue()
    {
        $exception = ResolverNotFoundException::createForValue(new DummyValue('dummy'));

        $this->assertEquals(
            'No resolver found to resolve value "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = ResolverNotFoundException::createForValue(new DummyValue('dummy'), $code, $previous);

        $this->assertEquals(
            'No resolver found to resolve value "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateNewExceptionWithFactoryForUnexpectedCall()
    {
        $exception = ResolverNotFoundException::createUnexpectedCall('fake');

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a resolver.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = ResolverNotFoundException::createUnexpectedCall('fake', $code, $previous);

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a resolver.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildResolverNotFoundException::createForParameter('foo');
        $this->assertInstanceOf(ChildResolverNotFoundException::class, $exception);

        $exception = ChildResolverNotFoundException::createForValue(new DummyValue('dummy'));
        $this->assertInstanceOf(ChildResolverNotFoundException::class, $exception);

        $exception = ChildResolverNotFoundException::createUnexpectedCall('fake');
        $this->assertInstanceOf(ChildResolverNotFoundException::class, $exception);
    }
}

class ChildResolverNotFoundException extends ResolverNotFoundException
{
}
