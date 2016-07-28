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

    public function testIsImmutable()
    {
        $loadedParameters = new ParameterBag(['foo' => 'bar']);
        $injectedParameters = new ParameterBag(['foo' => 'baz']);
        $fixtures = new FixtureBag();
        $injectedObjects = new ObjectBag([
            'dummy' => new \stdClass(),
        ]);

        $set = new FixtureSet($loadedParameters, $injectedParameters, $fixtures, $injectedObjects);

        $this->assertEquals($set->getInjectedParameters(), $set->getInjectedParameters());
        $this->assertEquals($set->getLoadedParameters(), $set->getLoadedParameters());
        $this->assertEquals($set->getFixtures(), $set->getFixtures());
        $this->assertEquals($set->getObjects(), $set->getObjects());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $loadedParameters = new ParameterBag(['foo' => 'bar']);
        $injectedParameters = new ParameterBag(['foo' => 'baz']);
        $fixtures = new FixtureBag();
        $injectedObjects = new ObjectBag([
            'dummy' => new \stdClass(),
        ]);

        $set = new FixtureSet($loadedParameters, $injectedParameters, $fixtures, $injectedObjects);
        clone $set;
    }
}
