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

use Error;
use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\FlagBag;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory
 */
class InvalidArgumentExceptionFactoryTest extends TestCase
{
    public function testCreateForInvalidReferenceType(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidReferenceType('foo');
        
        static::assertEquals(
            'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got "foo"'
            .' instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForReferenceKeyMismatch(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForReferenceKeyMismatch('foo', 'bar');

        static::assertEquals(
            'Reference key mismatch, the keys "foo" and "bar" refers to the same fixture but the keys are different.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForFlagBagKeyMismatch(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForFlagBagKeyMismatch(
            new DummyFixture('foo'),
            new FlagBag('bar')
        );

        static::assertEquals(
            'Expected the fixture ID and the flags key to be the same. Got "foo" and "bar" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidSeedConfigurationValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidSeedConfigurationValue(10);

        static::assertEquals(
            'Expected value to be either null or a strictly positive integer but got "10" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedConfigurationStringValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForExpectedConfigurationStringValue(10);

        static::assertEquals(
            'Expected a string value but got "integer" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedConfigurationPositiveIntegerValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForExpectedConfigurationPositiveIntegerValue(-1);

        static::assertEquals(
            'Expected a strictly positive integer but got "-1" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedConfigurationArrayOfStringValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForExpectedConfigurationArrayOfStringValue(10);

        static::assertEquals(
            'Expected an array of strings but got "integer" element in the array instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForRedundantUniqueValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForRedundantUniqueValue('foo');

        static::assertEquals(
            'Cannot create a unique value of a unique value for value "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidExpressionLanguageTokenType(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageTokenType('foo');

        static::assertEquals(
            'Expected type to be a known token type but got "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidExpressionLanguageToken(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageToken('foo');

        static::assertEquals(
            'Invalid token "foo" found.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForNoIncludeStatementInData(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForNoIncludeStatementInData('foo');

        static::assertEquals(
            'Could not find any include statement in the file "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForEmptyIncludedFileInData(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForEmptyIncludedFileInData('foo');

        static::assertEquals(
            'Expected elements of include statement to be file names. Got empty string instead in file "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForFileCouldNotBeFound(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForFileCouldNotBeFound('foo');

        static::assertEquals(
            'The file "foo" could not be found.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = InvalidArgumentExceptionFactory::createForFileCouldNotBeFound('foo', $code, $previous);

        static::assertEquals(
            'The file "foo" could not be found.',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidLimitValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidLimitValue(10);

        static::assertEquals(
            'Expected limit value to be a strictly positive integer, got "10" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidLimitValueForRecursiveCalls(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidLimitValueForRecursiveCalls(10);

        static::assertEquals(
            'Expected limit for recursive calls to be of at least 2. Got "10" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidFakerFormatter(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidFakerFormatter('foo');

        static::assertEquals(
            'Invalid faker formatter "foo" found.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForFixtureExtendingANonTemplateFixture(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForFixtureExtendingANonTemplateFixture(
            new DummyFixture('foo'),
            'bar'
        );

        static::assertEquals(
            'Fixture "foo" extends "bar" but "bar" is not a template.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForUnsupportedTypeForIdenticalValuesCheck(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForUnsupportedTypeForIdenticalValuesCheck(true);

        static::assertEquals(
            'Unsupported type "boolean": cannot determine if two values of this type are identical.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidConstructorMethod(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidConstructorMethod('foo');

        static::assertEquals(
            'Invalid constructor method "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidOptionalFlagBoundaries(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidOptionalFlagBoundaries(200);

        static::assertEquals(
            'Expected optional flag to be an integer element of [0;100]. Got "200" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidDynamicArrayQuantifier(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidDynamicArrayQuantifier(
            new DummyFixture('dummy'),
            200
        );

        static::assertEquals(
            'Expected quantifier to be a positive integer. Got "200" for "dummy", check you dynamic arrays '
            .'declarations (e.g. "<numberBetween(1, 2)>x @user*").',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
