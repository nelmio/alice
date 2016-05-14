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
 * @covers Nelmio\Alice\Instances\Instantiator\Methods\EmptyConstructor
 */
class EmptyConstructorInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmptyConstructor
     */
    private $instantiator;

    public function setUp()
    {
        $this->instantiator = new EmptyConstructor();
    }

    public function testIsAnInstantiatorMethod()
    {
        $this->assertTrue(
            is_a(
                'Nelmio\Alice\Instances\Instantiator\Methods\EmptyConstructor',
                'Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface',
                true
            )
        );
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

        $class = 'Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor';
        $fixture = $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor');
        $this->instantiator->canInstantiate($fixture);
        $actual = $this->instantiator->instantiate($fixture);

        $this->assertInstanceOf($class, $actual);
    }

    public function provideFixtures()
    {
        $returned = [];

        $returned['default constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithDefaultConstructor'),
            true,
        ];

        $returned['explicit default constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithExplicitDefaultConstructor'),
            true,
        ];

        $returned['constructor with optional parameter'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithOptionalParameterInConstructor'),
            true,
        ];


        $returned['private constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithPrivateConstructor'),
            false,
        ];

        $returned['protected constructor'] = [
            $this->createFixtureForClass('Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithProtectedConstructor'),
            false,
        ];

        $returned['named constructor'] = [
            new Fixture(
                'Nelmio\Alice\Instances\Instantiator\DummyClasses\DummyWithNamedConstructor',
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
     * @param string $class FQCN
     *
     * @return Fixture
     */
    private function createFixtureForClass($class)
    {
        return new Fixture($class, 'dummy', [], null);
    }
}
