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
 * @covers Nelmio\Alice\Fixture\SpecificationBag
 */
class SpecificationBagTest extends \PHPUnit_Framework_TestCase
{
    public function testIsNotDeepClonable()
    {
        $constructor = new MethodCallDefinition('setValue', []);
        $properties = (new PropertyDefinitionBag())->with(new PropertyDefinition('username', []));

        $bag = new SpecificationBag($constructor, $properties);
        $newBag = clone $bag;

        $this->assertInstanceOf(SpecificationBag::class, $bag);
        $this->assertEquals($bag, $newBag);
        $this->assertNotSame($bag, $newBag);
    }
}
