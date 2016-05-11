<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Chainable;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithExplicitDefaultConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithNamedConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalAndRequiredParameterInConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalParameterInConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithPrivateConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithProtectedConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithRequiredParameterInConstructor;
use PhpUnit\PhpUnit;

/**
 * @covers Nelmio\Alice\Instances\Instantiator\Chainable\ReflectionWithoutConstructorInstantiator
 */
class ReflectionWithoutConstructorInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectionWithoutConstructorInstantiator
     */
    private $instantiator;

    public function setUp()
    {
        $this->instantiator = new ReflectionWithoutConstructorInstantiator();
    }

    public function test_is_a_chainable_instantiator()
    {
        PhpUnit::assertIsA(ChainableInstantiatorInterface::class, ReflectionWithoutConstructorInstantiator::class);
    }

    /**
     * @dataProvider provideFixtures
     */
    public function test_can_instantiate_object_with_default_constructor(Fixture $fixture, bool $expected)
    {
        $actual = $this->instantiator->canInstantiate($fixture);

        $this->assertEquals($expected, $actual);
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

    public function provideFixtures()
    {
        $returned = [];

        $returned['private constructor'] = [
            $this->createFixtureForClass(DummyWithPrivateConstructor::class),
            true,
        ];

        $returned['protected constructor'] = [
            $this->createFixtureForClass(DummyWithProtectedConstructor::class),
            true,
        ];

        $returned['private constructor with fixture constructor different from __construct'] = [
            new Fixture(
                DummyWithPrivateConstructor::class,
                'dummy',
                [
                    '__construct' => [
                        'namedConstruct' => [],
                    ],
                ],
                null
            ),
            false,
        ];

        $returned['default constructor'] = [
            $this->createFixtureForClass(DummyWithDefaultConstructor::class),
            false,
        ];

        $returned['explicit default constructor'] = [
            $this->createFixtureForClass(DummyWithExplicitDefaultConstructor::class),
            false,
        ];

        $returned['named constructor'] = [
            new Fixture(
                DummyWithPrivateConstructor::class,
                'dummy',
                [
                    '__construct' => [
                        'namedConstruct' => [],
                    ],
                ],
                null
            ),
            false,
        ];

        $returned['constructor with optional parameter'] = [
            $this->createFixtureForClass(DummyWithOptionalParameterInConstructor::class),
            false,
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

    private function createFixtureForClass(string $class): Fixture
    {
        return new Fixture($class, 'dummy', [], null);
    }
}








