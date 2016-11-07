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
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithExplicitDefaultConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalAndRequiredParameterInConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalParameterInConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithPrivateConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithProtectedConstructor;
use Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithRequiredParameterInConstructor;

/**
 * @covers \Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithoutConstructor
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

    public function testIsAnInstantiatorMethod()
    {
        $this->assertTrue(is_a(ReflectionWithoutConstructor::class, MethodInterface::class, true));
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testCanInstantiateObjectWithDefaultConstructor(Fixture $fixture, $expected)
    {
        $actual = $this->instantiator->canInstantiate($fixture);

        $this->assertEquals($expected, $actual);
    }

    public function testInstantiateFixture()
    {
        $class = 'stdClass';
        $fixture = $this->createFixtureForClass($class);
        $this->instantiator->canInstantiate($fixture);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf($class, $actual);

        $class = DummyWithDefaultConstructor::class;
        $fixture = $this->createFixtureForClass(DummyWithDefaultConstructor::class);
        $this->instantiator->canInstantiate($fixture);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf($class, $actual);
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
