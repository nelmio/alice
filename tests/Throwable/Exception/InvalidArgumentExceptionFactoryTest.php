<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\Throwable\Exception;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\FlagBag;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory
 */
class InvalidArgumentExceptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testTestCreateForInvalidReferenceType()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidReferenceType('foo');
        
        $this->assertEquals(
            'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got "foo"'
            .' instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForReferenceKeyMismatch()
    {
        $exception = InvalidArgumentExceptionFactory::createForReferenceKeyMismatch('foo', 'bar');

        $this->assertEquals(
            'Reference key mismatch, the keys "foo" and "bar" refers to the same fixture but the keys are different.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForFlagBagKeyMismatch()
    {
        $exception = InvalidArgumentExceptionFactory::createForFlagBagKeyMismatch(
            new DummyFixture('foo'),
            new FlagBag('bar')
        );

        $this->assertEquals(
            'Expected the fixture ID and the flags key to be the same. Got "foo" and "bar" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidSeedConfigurationValue()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidSeedConfigurationValue(10);

        $this->assertEquals(
            'Expected value "nelmio_alice.seed" to be either null or a strictly positive integer but got "10" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForRedundantUniqueValue()
    {
        $exception = InvalidArgumentExceptionFactory::createForRedundantUniqueValue('foo');

        $this->assertEquals(
            'Cannot create a unique value of a unique value for value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidExpressionLanguageTokenType()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageTokenType('foo');

        $this->assertEquals(
            'Expected type to be a known token type but got "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidExpressionLanguageToken()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageToken('foo');

        $this->assertEquals(
            'Invalid token "foo" found.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForNoIncludeStatementInData()
    {
        $exception = InvalidArgumentExceptionFactory::createForNoIncludeStatementInData('foo');

        $this->assertEquals(
            'Could not find any include statement in the file "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForEmptyIncludedFileInData()
    {
        $exception = InvalidArgumentExceptionFactory::createForEmptyIncludedFileInData('foo');

        $this->assertEquals(
            'Expected elements of include statement to be file names. Got empty string instead in file "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForFileCouldNotBeFound()
    {
        $exception = InvalidArgumentExceptionFactory::createForFileCouldNotBeFound('foo');

        $this->assertEquals(
            'The file "foo" could not be found.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = InvalidArgumentExceptionFactory::createForFileCouldNotBeFound('foo', $code, $previous);

        $this->assertEquals(
            'The file "foo" could not be found.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateForInvalidLimitValue()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidLimitValue(10);

        $this->assertEquals(
            'Expected limit value to be a strictly positive integer, got "10" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidLimitValueForRecursiveCalls()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidLimitValueForRecursiveCalls(10);

        $this->assertEquals(
            'Expected limit for recursive calls to be of at least 2. Got "10" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidFakerFormatter()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidFakerFormatter('foo');

        $this->assertEquals(
            'Invalid faker formatter "foo" found.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForFixtureExtendingANonTemplateFixture()
    {
        $exception = InvalidArgumentExceptionFactory::createForFixtureExtendingANonTemplateFixture(
            new DummyFixture('foo'),
            'bar'
        );

        $this->assertEquals(
            'Fixture "foo" extends "bar" but "bar" is not a template.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForUnsupportedTypeForIdenticalValuesCheck()
    {
        $exception = InvalidArgumentExceptionFactory::createForUnsupportedTypeForIdenticalValuesCheck(true);

        $this->assertEquals(
            'Unsupported type "boolean": cannot determine if two values of this type are identical.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidConstructorMethod()
    {
        $exception = InvalidArgumentExceptionFactory::createForInvalidConstructorMethod('foo');

        $this->assertEquals(
            'Invalid constructor method "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
