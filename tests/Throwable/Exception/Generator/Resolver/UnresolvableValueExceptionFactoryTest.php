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
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory
 */
class UnresolvableValueExceptionFactoryTest extends TestCase
{
    public function testCreate()
    {
        $exception = UnresolvableValueExceptionFactory::create(new DummyValue('dummy'));

        $this->assertEquals(
            'Could not resolve value "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = UnresolvableValueExceptionFactory::create(new DummyValue('dummy'), $code, $previous);
        $this->assertEquals(
            'Could not resolve value "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidReferenceId()
    {
        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), 100);

        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(integer) 100" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), 'alice');

        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(string) alice" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), new \stdClass());

        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "stdClass" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), 100, $code, $previous);

        $this->assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(integer) 100" instead.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotEvaluateExpression()
    {
        $exception = UnresolvableValueExceptionFactory::createForCouldNotEvaluateExpression(new DummyValue('dummy'));

        $this->assertEquals(
            'Could not evaluate the expression "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = UnresolvableValueExceptionFactory::createForCouldNotEvaluateExpression(new DummyValue('dummy'), $code, $previous);

        $this->assertEquals(
            'Could not evaluate the expression "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotFindVariable()
    {
        $exception = UnresolvableValueExceptionFactory::createForCouldNotFindVariable(new DummyValue('dummy'));

        $this->assertEquals(
            'Could not find a variable "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = UnresolvableValueExceptionFactory::createForCouldNotFindVariable(new DummyValue('dummy'), $code, $previous);

        $this->assertEquals(
            'Could not find a variable "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotFindParameter()
    {
        $exception = UnresolvableValueExceptionFactory::createForCouldNotFindParameter('foo');

        $this->assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidResolvedQuantifierTypeForOptionalValue()
    {
        $quantifier = new DummyValue('quantifier');

        $exception = UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($quantifier, null);

        $this->assertEquals(
            'Expected the quantifier "Nelmio\Alice\Definition\Value\DummyValue" for the optional value to be resolved '
            .'into a string, got "NULL" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($quantifier, new \stdClass());

        $this->assertEquals(
            'Expected the quantifier "Nelmio\Alice\Definition\Value\DummyValue" for the optional value to be resolved '
            .'into a string, got "stdClass" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($quantifier, []);

        $this->assertEquals(
            'Expected the quantifier "Nelmio\Alice\Definition\Value\DummyValue" for the optional value to be resolved '
            .'into a string, got "array" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForNoFixtureOrObjectMatchingThePattern()
    {
        $exception = UnresolvableValueExceptionFactory::createForNoFixtureOrObjectMatchingThePattern(
            new DummyValue('/foo/')
        );

        $this->assertEquals(
            'Could not find a fixture or object ID matching the pattern "/foo/".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
