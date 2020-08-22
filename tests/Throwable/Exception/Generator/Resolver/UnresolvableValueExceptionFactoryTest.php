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

use Error;
use Nelmio\Alice\Definition\Value\DummyValue;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory
 */
class UnresolvableValueExceptionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $exception = UnresolvableValueExceptionFactory::create(new DummyValue('dummy'));

        static::assertEquals(
            'Could not resolve value "dummy".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = UnresolvableValueExceptionFactory::create(new DummyValue('dummy'), $code, $previous);
        static::assertEquals(
            'Could not resolve value "dummy".',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidReferenceId(): void
    {
        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), 100);

        static::assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(integer) 100" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), 'alice');

        static::assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(string) alice" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), new stdClass());

        static::assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "stdClass" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = UnresolvableValueExceptionFactory::createForInvalidReferenceId(new DummyValue('dummy'), 100, $code, $previous);

        static::assertEquals(
            'Expected fixture reference value "dummy" to be resolved into a string. Got "(integer) 100" instead.',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotEvaluateExpression(): void
    {
        $exception = UnresolvableValueExceptionFactory::createForCouldNotEvaluateExpression(new DummyValue('dummy'));

        static::assertEquals(
            'Could not evaluate the expression "dummy".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = UnresolvableValueExceptionFactory::createForCouldNotEvaluateExpression(new DummyValue('dummy'), $code, $previous);

        static::assertEquals(
            'Could not evaluate the expression "dummy".',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotFindVariable(): void
    {
        $exception = UnresolvableValueExceptionFactory::createForCouldNotFindVariable(new DummyValue('dummy'));

        static::assertEquals(
            'Could not find a variable "dummy".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = UnresolvableValueExceptionFactory::createForCouldNotFindVariable(new DummyValue('dummy'), $code, $previous);

        static::assertEquals(
            'Could not find a variable "dummy".',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotFindParameter(): void
    {
        $exception = UnresolvableValueExceptionFactory::createForCouldNotFindParameter('foo');

        static::assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidResolvedQuantifierTypeForOptionalValue(): void
    {
        $quantifier = new DummyValue('quantifier');

        $exception = UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($quantifier, null);

        static::assertEquals(
            'Expected the quantifier "Nelmio\Alice\Definition\Value\DummyValue" for the optional value to be resolved '
            .'into a string, got "NULL" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($quantifier, new stdClass());

        static::assertEquals(
            'Expected the quantifier "Nelmio\Alice\Definition\Value\DummyValue" for the optional value to be resolved '
            .'into a string, got "stdClass" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $exception = UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($quantifier, []);

        static::assertEquals(
            'Expected the quantifier "Nelmio\Alice\Definition\Value\DummyValue" for the optional value to be resolved '
            .'into a string, got "array" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForNoFixtureOrObjectMatchingThePattern(): void
    {
        $exception = UnresolvableValueExceptionFactory::createForNoFixtureOrObjectMatchingThePattern(
            new DummyValue('/foo/')
        );

        static::assertEquals(
            'Could not find a fixture or object ID matching the pattern "/foo/".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
