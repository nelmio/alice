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
 * @internal
 */
class ResolvedFixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        self::assertEquals($parameters, $set->getParameters());
        self::assertEquals($fixtures, $set->getFixtures());
        self::assertEquals($objects, $set->getObjects());
    }

    public function testWithersReturnANewModifiedInstance(): void
    {
        $parameters = new ParameterBag();
        $fixtures = new FixtureBag();
        $objects = new ObjectBag();

        $set = new ResolvedFixtureSet($parameters, $fixtures, $objects);

        $newParameters = new ParameterBag(['foo' => 'bar']);
        $newSet = $set->withParameters($newParameters);

        self::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set,
        );
        self::assertEquals(
            new ResolvedFixtureSet($newParameters, $fixtures, $objects),
            $newSet,
        );

        $newFixtures = new FixtureBag();
        $newSet = $set->withFixtures($newFixtures);

        self::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set,
        );
        self::assertEquals(
            new ResolvedFixtureSet($parameters, $newFixtures, $objects),
            $newSet,
        );

        $newObjects = new ObjectBag(['foo' => new stdClass()]);
        $newSet = $set->withObjects($newObjects);

        self::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $objects),
            $set,
        );
        self::assertEquals(
            new ResolvedFixtureSet($parameters, $fixtures, $newObjects),
            $newSet,
        );
    }
}
