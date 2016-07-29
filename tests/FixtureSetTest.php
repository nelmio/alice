<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

/**
 * @covers Nelmio\Alice\FixtureSet
 */
class FixtureSetTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @depends Nelmio\Alice\ParameterBagTest::testIsImmutable
     * @depends Nelmio\Alice\FixtureBagTest::testIsImmutable
     * @depends Nelmio\Alice\ObjectBagTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }
}
