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

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\ResolvedFixtureSet
 */
class ResolvedFixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        static::assertEquals($parameters, $set->getParameters());
        static::assertEquals($fixtures, $set->getFixtures());
        static::assertEquals($objects, $set->getObjects());
    }

    public function testWithersReturnANewModifiedInstance(): void
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        $newParameters = new ParameterBag(['foo' => 'bar']);
        $newSet = $set->withParameters($newParameters);

        static::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set
        );
        static::assertEquals(
            new ResolvedFixtureSet($newParameters, $fixtures, $objects),
            $newSet
        );

        $newFixtures = new FixtureBag();
        $newSet = $set->withFixtures($newFixtures);

        static::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set
        );
        static::assertEquals(
            new ResolvedFixtureSet($parameters, $newFixtures, $objects),
            $newSet
        );

        $newObjects = new ObjectBag(['foo' => new stdClass()]);
        $newSet = $set->withObjects($newObjects);

        static::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set
        );
        static::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $newObjects),
            $newSet
        );
    }
}
