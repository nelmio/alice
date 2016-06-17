<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers Nelmio\Alice\Definition\ServiceReference\FixtureReference
 */
class FixtureReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAReference()
    {
        $this->assertTrue(is_a(FixtureReference::class, ServiceReferenceInterface::class, true));
    }
    
    public function testAccessors()
    {
        $reference = 'Nelmio\Entity\User#user_base';
        $definition = new FixtureReference($reference);
        
        $this->assertEquals($reference, $definition->getReference());
    }

    public function testCreateAbsoluteReference()
    {
        $reference = 'user_base';
        $definition = new FixtureReference($reference);

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $absoluteDefinition = $definition->createAbsoluteFrom($fixture);

        $this->assertEquals($reference, $definition->getReference());
        $this->assertEquals('Nelmio\Entity\User#user_base', $absoluteDefinition->getReference());

        $fixtureProphecy->getClassName()->shouldHaveBeenCalledTimes(1);
    }

    public function testCreateAbsoluteReferenceFromAbsoluteReference()
    {
        $reference = 'Nelmio\Entity\User#user_base';
        $definition = new FixtureReference($reference);

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('Nelmio\Entity\User#user0');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        try {
            $definition->createAbsoluteFrom($fixture);
            $this->fail('Expected exception to be thrown.');
        } catch (\BadMethodCallException $exception) {
            $this->assertEquals(
                'Attempted to make the reference "Nelmio\Entity\User#user_base" absolute from the fixture of ID '
                .'"Nelmio\Entity\User#user0", however the reference is already absolute.',
                $exception->getMessage()
            );
        }

        $fixtureProphecy->getId()->shouldHaveBeenCalledTimes(1);
    }
}
