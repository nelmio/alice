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
        $reference = 'Nelmio\Alice\User#user_base';
        $definition = new FixtureReference($reference);
        
        $this->assertEquals($reference, $definition->getReference());
    }
}
