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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Generator\ResolvedFixtureSet
 */
class ResolvedFixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        $this->assertEquals($parameters, $set->getParameters());
        $this->assertEquals($fixtures, $set->getFixtures());
        $this->assertEquals($objects, $set->getObjects());
    }

    public function testWithersReturnANewModifiedInstance()
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        $newParameters = new ParameterBag(['foo' => 'bar']);
        $newSet = $set->withParameters($newParameters);

        $this->assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set
        );
        $this->assertEquals(
            new ResolvedFixtureSet($newParameters, $fixtures, $objects),
            $newSet
        );

        $newFixtures = new FixtureBag(['foo' => new DummyFixture('foo')]);
        $newSet = $set->withFixtures($newFixtures);

        $this->assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set
        );
        $this->assertEquals(
            new ResolvedFixtureSet($parameters, $newFixtures, $objects),
            $newSet
        );

        $newObjects = new ObjectBag(['foo' => new \stdClass()]);
        $newSet = $set->withObjects($newObjects);

        $this->assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set
        );
        $this->assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $newObjects),
            $newSet
        );
    }
}
