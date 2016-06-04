<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ParameterBag;

/**
 * @covers Nelmio\Alice\FixtureBuilder\BareFixtureSet
 */
class BareFixtureSetTest extends \PHPUnit_Framework_TestCase
{
    public function testIsImmutable()
    {
        $parameters = new ParameterBag(['foo' => 'bar']);
        $fixtures = new FixtureBag(['std' => new \stdClass()]);

        $set = new BareFixtureSet($parameters, $fixtures);

        $this->assertNotSame($set->getParameters(), $set->getParameters());
        $this->assertNotSame($set->getFixtures(), $set->getFixtures());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $set = new BareFixtureSet(new ParameterBag(), new FixtureBag());
        clone $set;
    }
}
