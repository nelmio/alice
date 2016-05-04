<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Chainable;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface;
use PhpUnit\PhpUnit;

/**
 * @covers Nelmio\Alice\Instances\Instantiator\Chainable\EmptyConstructorInstantiator
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EmptyConstructorInstantiatorInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmptyConstructorInstantiator
     */
    private $instantiator;

    public function setUp()
    {
        $this->instantiator = new EmptyConstructorInstantiator();
    }

    public function test_is_a_chainable_instantiator()
    {
        PhpUnit::assertIsA(ChainableInstantiatorInterface::class, EmptyConstructorInstantiator::class);
    }

    /**
     * @dataProvider provideFixtures
     */
    public function test_can_instantiate_object_with_default_constructor(Fixture $fixture, bool $expected)
    {
        $actual = $this->instantiator->canInstantiate($fixture);

        $this->assertEquals($expected, $actual);
    }

    public function provideFixtures()
    {
        $returned = [];

        $returned['default constructor'] = [
            $this->createFixtureForClass(DummyWithDefaultConstructor::class),
            true,
        ];

        $returned['private constructor'] = [
            $this->createFixtureForClass(DummyWithPrivateConstructor::class),
            true,
        ];

        $returned['explicit default constructor'] = [
            $this->createFixtureForClass(DummyWithExplicitDefaultConstructor::class),
            true,
        ];

        $returned['constructor with optional parameter'] = [
            $this->createFixtureForClass(DummyWithOptionalParameterInConstructor::class),
            true,
        ];

        $returned['constructor with required parameter'] = [
            $this->createFixtureForClass(DummyWithRequiredParameterInConstructor::class),
            false,
        ];

        $returned['constructor with optional and required parameter'] = [
            $this->createFixtureForClass(DummyWithOptionalAndRequiredParameterInConstructor::class),
            false,
        ];

        return $returned;
    }

    public function test_instantiate_fixture()
    {
        $fixture = $this->createFixtureForClass(\stdClass::class);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf(\stdClass::class, $actual);

        $fixture = $this->createFixtureForClass(DummyWithDefaultConstructor::class);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf(DummyWithDefaultConstructor::class, $actual);
    }

    private function createFixtureForClass(string $class): Fixture
    {
        return new Fixture($class, 'dummy', [], null);
    }
}

class DummyWithDefaultConstructor
{
}

class DummyWithPrivateConstructor
{
    private function __construct()
    {
    }
}

class DummyWithExplicitDefaultConstructor
{
    public function __construct()
    {
    }
}

class DummyWithOptionalParameterInConstructor
{
    public function __construct($optionalParam = 10)
    {
    }
}

class DummyWithRequiredParameterInConstructor
{
    public function __construct($requiredParam)
    {
    }
}

class DummyWithOptionalAndRequiredParameterInConstructor
{
    public function __construct($requiredParam, $optionalParam = 10)
    {
    }
}
