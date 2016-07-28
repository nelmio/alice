<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;

/**
 * @covers Nelmio\Alice\Generator\ResolvedFixtureSet
 */
class ResolvedFixtureSetTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        $this->assertEquals($parameters, $set->getParameters());
        $this->assertEquals($fixtures, $set->getFixtures());
        $this->assertEquals($objects, $set->getObjects());
    }

    public function testImmutability()
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        $this->assertNotSame($set->getParameters(), $set->getParameters());
        $this->assertNotSame($set->getFixtures(), $set->getFixtures());
        $this->assertNotSame($set->getObjects(), $set->getObjects());
    }

    public function testIsDeepClonable()
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);
        $clone = clone $set;

        $reflClass = new \ReflectionClass(ResolvedFixtureSet::class);
        $parametersRefl = $reflClass->getProperty('parameters');
        $parametersRefl->setAccessible(true);
        $fixturesRefl = $reflClass->getProperty('fixtures');
        $fixturesRefl->setAccessible(true);
        $objectsRefl = $reflClass->getProperty('objects');
        $objectsRefl->setAccessible(true);

        $this->assertInstanceOf(ResolvedFixtureSet::class, $clone);
        $this->assertNotSame($set, $clone);
        $this->assertNotSame($parametersRefl->getValue($set), $parametersRefl->getValue($clone));
        $this->assertNotSame($fixturesRefl->getValue($set), $fixturesRefl->getValue($clone));
        $this->assertNotSame($objectsRefl->getValue($set), $objectsRefl->getValue($clone));
    }
}
