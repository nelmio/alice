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

/**
 * @covers \Nelmio\Alice\FixtureSet
 */
class FixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $loadedParameters = new ParameterBag(['foo' => 'bar']);
        $injectedParameters = new ParameterBag(['foo' => 'baz']);
        $fixtures = new FixtureBag();
        $injectedObjects = new ObjectBag([
            'dummy' => new \stdClass(),
        ]);

        $set = new FixtureSet($loadedParameters, $injectedParameters, $fixtures, $injectedObjects);

        $this->assertEquals($loadedParameters, $set->getLoadedParameters());
        $this->assertEquals($injectedParameters, $set->getInjectedParameters());
        $this->assertEquals($fixtures, $set->getFixtures());
        $this->assertEquals($injectedObjects, $set->getObjects());
    }
}
