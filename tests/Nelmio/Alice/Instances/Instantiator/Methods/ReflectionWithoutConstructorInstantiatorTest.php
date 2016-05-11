<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

/**
 * @covers Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithoutConstructor
 */
class ReflectionWithoutConstructorInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectionWithoutConstructor
     */
    private $instantiator;

    public function setUp()
    {
        $this->instantiator = new ReflectionWithoutConstructor();
    }

    public function test_is_an_instantiator_method()
    {
        $this->assertTrue(
            is_a(
                'Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithoutConstructor',
                'Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface',
                true
            )
        );
    }

    /**
     * @dataProvider provideFixtures
     */
    public function test_can_instantiate_object_with_default_constructor(Fixture $fixture, $expected)
    {
        $actual = $this->instantiator->canInstantiate($fixture);

        $this->assertEquals($expected, $actual);
    }

    public function test_instantiate_fixture()
    {
        $class = 'stdClass';
        $fixture = $this->createFixtureForClass($class);
        $this->instantiator->canInstantiate($fixture);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf($class, $actual);

        $class = 'Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor';
        $fixture = $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor');
        $this->instantiator->canInstantiate($fixture);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf($class, $actual);
    }

    public function provideFixtures()
    {
        $returned = [];

        $returned['private constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithPrivateConstructor'),
            true,
        ];

        $returned['protected constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithProtectedConstructor'),
            true,
        ];

        $returned['private constructor with fixture constructor different from __construct'] = [
            new Fixture(
                'Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithPrivateConstructor',
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
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor'),
            false,
        ];

        $returned['explicit default constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithExplicitDefaultConstructor'),
            false,
        ];

        $returned['named constructor'] = [
            new Fixture(
                'Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithPrivateConstructor',
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
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalParameterInConstructor'),
            false,
        ];

        $returned['constructor with required parameter'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithRequiredParameterInConstructor'),
            false,
        ];

        $returned['constructor with optional and required parameter'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalAndRequiredParameterInConstructor'),
            false,
        ];

        return $returned;
    }

    /**
     * @param string $class
     *
     * @return Fixture
     */
    private function createFixtureForClass($class)
    {
        return new Fixture($class, 'dummy', [], null);
    }
}
