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

namespace Nelmio\Alice;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureSet
 * @internal
 */
class FixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $loadedParameters = new ParameterBag(['foo' => 'bar']);
        $injectedParameters = new ParameterBag(['foo' => 'baz']);
        $fixtures = new FixtureBag();
        $injectedObjects = new ObjectBag([
            'dummy' => new stdClass(),
        ]);

        $set = new FixtureSet($loadedParameters, $injectedParameters, $fixtures, $injectedObjects);

        self::assertEquals($loadedParameters, $set->getLoadedParameters());
        self::assertEquals($injectedParameters, $set->getInjectedParameters());
        self::assertEquals($fixtures, $set->getFixtures());
        self::assertEquals($injectedObjects, $set->getObjects());
    }
}
