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

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
 */
class UnresolvableValueExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(UnresolvableValueException::class, \RuntimeException::class, true));
    }

    public function testIsAResolutionThrowable()
    {
        $this->assertTrue(is_a(UnresolvableValueException::class, ResolutionThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = UnresolvableValueException::create(new DummyValue('dummy'));
        $this->assertEquals(
            'Could not resolve value "dummy".',
            $exception->getMessage()
        );

        $code = 100;
        $previous = new \Error();

        $exception = UnresolvableValueException::create(new DummyValue('dummy'), $code, $previous);
        $this->assertEquals(
            'Could not resolve value "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateExceptionWithFactoryForInvalidReferenceId()
    {
        $exception = UnresolvableValueException::createForInvalidReferenceId(new DummyValue('dummy'), 100);
        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(integer) 100" instead.',
            $exception->getMessage()
        );

        $exception = UnresolvableValueException::createForInvalidReferenceId(new DummyValue('dummy'), 'alice');
        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(string) alice" instead.',
            $exception->getMessage()
        );

        $exception = UnresolvableValueException::createForInvalidReferenceId(new DummyValue('dummy'), new \stdClass());
        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "stdClass" instead.',
            $exception->getMessage()
        );

        $code = 500;
        $previous = new \Error();

        $exception = UnresolvableValueException::createForInvalidReferenceId(new DummyValue('dummy'), 100, $code, $previous);
        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(integer) 100" instead.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateExceptionForAnExpressionThatCouldNotHaveBeenEvaluated()
    {
        $exception = UnresolvableValueException::couldNotEvaluateExpression(new DummyValue('dummy'));
        $this->assertEquals(
            'Could not evaluate the expression "dummy".',
            $exception->getMessage()
        );

        $code = 500;
        $previous = new \Error();

        $exception = UnresolvableValueException::couldNotEvaluateExpression(new DummyValue('dummy'), $code, $previous);
        $this->assertEquals(
            'Could not evaluate the expression "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildUnresolvableValueException::create(new DummyValue('dummy'));
        $this->assertInstanceOf(ChildUnresolvableValueException::class, $exception);

        $exception = ChildUnresolvableValueException::createForInvalidReferenceId(new DummyValue('dummy'), new \stdClass());
        $this->assertInstanceOf(ChildUnresolvableValueException::class, $exception);

        $exception = ChildUnresolvableValueException::couldNotEvaluateExpression(new DummyValue('dummy'));
        $this->assertInstanceOf(ChildUnresolvableValueException::class, $exception);
    }
}

class ChildUnresolvableValueException extends UnresolvableValueException
{
}
