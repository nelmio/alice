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

namespace Nelmio\Alice\Throwable\Exception\Generator\Instantiator;

use Error;
use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationExceptionFactory
 */
class InstantiationExceptionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $code = 500;
        $previous = new Error();
        $exception = InstantiationExceptionFactory::create(new DummyFixture('foo'), $code, $previous);

        static::assertEquals(
            'Could not instantiate fixture "foo".',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForNonPublicConstructor(): void
    {
        $exception = InstantiationExceptionFactory::createForNonPublicConstructor(
            new SimpleFixture('foo', 'Dummy', SpecificationBagFactory::create())
        );

        static::assertEquals(
            'Could not instantiate "foo", the constructor of "Dummy" is not public.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForConstructorIsMissingMandatoryParameters(): void
    {
        $exception = InstantiationExceptionFactory::createForConstructorIsMissingMandatoryParameters(
            new DummyFixture('foo')
        );

        static::assertEquals(
            'Could not instantiate "foo", the constructor has mandatory parameters but no parameters have been given.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForCouldNotGetConstructorData(): void
    {
        $exception = InstantiationExceptionFactory::createForCouldNotGetConstructorData(
            new DummyFixture('foo')
        );

        static::assertEquals(
            'Could not get the necessary data on the constructor to instantiate "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidInstanceType(): void
    {
        $exception = InstantiationExceptionFactory::createForInvalidInstanceType(
            new SimpleFixture('foo', 'Dummy', SpecificationBagFactory::create()),
            new stdClass()
        );

        static::assertEquals(
            'Instantiated fixture was expected to be an instance of "Dummy". Got "stdClass" instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInstantiatorNotFoundForFixture(): void
    {
        $exception = InstantiationExceptionFactory::createForInstantiatorNotFoundForFixture(
            new DummyFixture('foo')
        );

        static::assertEquals(
            'No suitable instantiator found for the fixture "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
