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
}
