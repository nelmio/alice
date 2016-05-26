<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

/**
 * @covers Nelmio\Alice\Fixture\PropertyDefinitionBag
 */
class PropertyDefinitionBagTest extends \PHPUnit_Framework_TestCase
{
    public function testImmutableMutator()
    {
        $property = new PropertyDefinition('username', 'Alice');
        $bag = new PropertyDefinitionBag();
        $newBag = $bag->with($property);
        
        $this->assertInstanceOf(PropertyDefinitionBag::class, $newBag);
        $this->assertNotSame($newBag, $bag);
    }
}
