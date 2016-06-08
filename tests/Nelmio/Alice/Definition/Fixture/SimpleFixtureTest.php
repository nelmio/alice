<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\SpecificationBag;

/**
 * @covers Nelmio\Alice\Definition\Fixture\SimpleFixture
 */
class SimpleFixtureTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFixture()
    {
        $this->assertTrue(is_a(SimpleFixture::class, FixtureInterface::class, true));
    }
    
    public function testAccessors()
    {
        $reference = 'user0';
        $className = 'Nelmio\Entity\User';
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());

        $fixture = new SimpleFixture($reference, $className, $specs);

        $this->assertEquals('Nelmio\Entity\User#user0', $fixture->getId());
        $this->assertEquals($reference, $fixture->getReference());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
    }

    public function testIsImmutable()
    {
        $reference = 'user0';
        $className = 'Nelmio\Entity\User';
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());

        $fixture = new SimpleFixture($reference, $className, $specs);

        $this->assertNotSame($fixture->getSpecs(), $fixture->getSpecs());
    }

    public function testIsDeepClonable()
    {
        $reference = 'user0';
        $className = 'Nelmio\Entity\User';
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());

        $fixture = new SimpleFixture($reference, $className, $specs);
        $clone = clone $fixture;

        $this->assertEquals($fixture, $clone);
        $this->assertNotSame($fixture, $clone);
    }

    public function testImmutableMutators()
    {
        $reference = 'user0';
        $className = 'Nelmio\Entity\User';
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $newSpecs = new SpecificationBag(new DummyMethodCall('dummy'), new PropertyBag(), new MethodCallBag());

        $fixture = new SimpleFixture($reference, $className, $specs);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(SimpleFixture::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
    }
}
