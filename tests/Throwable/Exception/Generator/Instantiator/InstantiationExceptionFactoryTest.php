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

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationExceptionFactory
 */
class InstantiationExceptionFactoryTest extends TestCase
{
    public function testCreate()
    {
        $code = 500;
        $previous = new \Error();
        $exception = InstantiationExceptionFactory::create(new DummyFixture('foo'), $code, $previous);

        $this->assertEquals(
            'Could not instantiate fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForNonPublicConstructor()
    {
        $exception = InstantiationExceptionFactory::createForNonPublicConstructor(
            new SimpleFixture('foo', 'Dummy', SpecificationBagFactory::create())
        );

        $this->assertEquals(
            'Could not instantiate "foo", the constructor of "Dummy" is not public.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForConstructorIsMissingMandatoryParameters()
    {
        $exception = InstantiationExceptionFactory::createForConstructorIsMissingMandatoryParameters(
            new DummyFixture('foo')
        );

        $this->assertEquals(
            'Could not instantiate "foo", the constructor has mandatory parameters but no parameters have been given.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForCouldNotGetConstructorData()
    {
        $exception = InstantiationExceptionFactory::createForCouldNotGetConstructorData(
            new DummyFixture('foo')
        );

        $this->assertEquals(
            'Could not get the necessary data on the constructor to instantiate "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidInstanceType()
    {
        $exception = InstantiationExceptionFactory::createForInvalidInstanceType(
            new SimpleFixture('foo', 'Dummy', SpecificationBagFactory::create()),
            new \stdClass()
        );

        $this->assertEquals(
            'Instantiated fixture was expected to be an instance of "Dummy". Got "stdClass" instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForInstantiatorNotFoundForFixture()
    {
        $exception = InstantiationExceptionFactory::createForInstantiatorNotFoundForFixture(
            new DummyFixture('foo')
        );

        $this->assertEquals(
            'No suitable instantiator found for the fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
