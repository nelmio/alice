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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(InvalidArgumentExceptionFactory::class)]
final class InvalidArgumentExceptionFactoryTest extends TestCase
{
    public function testCreateForInvalidReferenceType(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidReferenceType('foo');

        self::assertEquals(
            'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got "foo"'
            .' instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForReferenceKeyMismatch(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForReferenceKeyMismatch('foo', 'bar');

        self::assertEquals(
            'Reference key mismatch, the keys "foo" and "bar" refers to the same fixture but the keys are different.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForFlagBagKeyMismatch(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForFlagBagKeyMismatch(
            new DummyFixture('foo'),
            new FlagBag('bar'),
        );

        self::assertEquals(
            'Expected the fixture ID and the flags key to be the same. Got "foo" and "bar" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidSeedConfigurationValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidSeedConfigurationValue(10);

        self::assertEquals(
            'Expected value to be either null or a strictly positive integer but got "10" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedConfigurationStringValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForExpectedConfigurationStringValue(10);

        self::assertEquals(
            'Expected a string value but got "integer" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedConfigurationPositiveIntegerValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForExpectedConfigurationPositiveIntegerValue(-1);

        self::assertEquals(
            'Expected a strictly positive integer but got "-1" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedConfigurationArrayOfStringValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForExpectedConfigurationArrayOfStringValue(10);

        self::assertEquals(
            'Expected an array of strings but got "integer" element in the array instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForRedundantUniqueValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForRedundantUniqueValue('foo');

        self::assertEquals(
            'Cannot create a unique value of a unique value for value "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidExpressionLanguageTokenType(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageTokenType('foo');

        self::assertEquals(
            'Expected type to be a known token type but got "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidExpressionLanguageToken(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageToken('foo');

        self::assertEquals(
            'Invalid token "foo" found.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForNoIncludeStatementInData(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForNoIncludeStatementInData('foo');

        self::assertEquals(
            'Could not find any include statement in the file "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForEmptyIncludedFileInData(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForEmptyIncludedFileInData('foo');

        self::assertEquals(
            'Expected elements of include statement to be file names. Got empty string instead in file "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForFileCouldNotBeFound(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForFileCouldNotBeFound('foo');

        self::assertEquals(
            'The file "foo" could not be found.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = InvalidArgumentExceptionFactory::createForFileCouldNotBeFound('foo', $code, $previous);

        self::assertEquals(
            'The file "foo" could not be found.',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidLimitValue(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidLimitValue(10);

        self::assertEquals(
            'Expected limit value to be a strictly positive integer, got "10" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidLimitValueForRecursiveCalls(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidLimitValueForRecursiveCalls(10);

        self::assertEquals(
            'Expected limit for recursive calls to be of at least 2. Got "10" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidFakerFormatter(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidFakerFormatter('foo');

        self::assertEquals(
            'Invalid faker formatter "foo" found.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForFixtureExtendingANonTemplateFixture(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForFixtureExtendingANonTemplateFixture(
            new DummyFixture('foo'),
            'bar',
        );

        self::assertEquals(
            'Fixture "foo" extends "bar" but "bar" is not a template.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForUnsupportedTypeForIdenticalValuesCheck(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForUnsupportedTypeForIdenticalValuesCheck(true);

        self::assertEquals(
            'Unsupported type "boolean": cannot determine if two values of this type are identical.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidConstructorMethod(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidConstructorMethod('foo');

        self::assertEquals(
            'Invalid constructor method "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidOptionalFlagBoundaries(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidOptionalFlagBoundaries(200);

        self::assertEquals(
            'Expected optional flag to be an integer element of [0;100]. Got "200" instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidDynamicArrayQuantifier(): void
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidDynamicArrayQuantifier(
            new DummyFixture('dummy'),
            200,
        );

        self::assertEquals(
            'Expected quantifier to be a positive integer. Got "200" for "dummy", check you dynamic arrays '
            .'declarations (e.g. "<numberBetween(1, 2)>x @user*").',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
