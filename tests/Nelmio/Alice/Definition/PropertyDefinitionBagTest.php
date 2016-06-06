<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

/**
 * @covers Nelmio\Alice\Definition\PropertyDefinitionBag
 */
class PropertyDefinitionBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $refl = new \ReflectionClass(PropertyDefinitionBag::class);
        $propRefl = $refl->getProperty('properties');
        $propRefl->setAccessible(true);

        $this->propRefl = $propRefl;
    }

    public function testImmutableMutator()
    {
        $property = new PropertyDefinition('username', 'alice');

        $bag = new PropertyDefinitionBag();
        $newBag = $bag->with($property);

        $this->assertInstanceOf(PropertyDefinitionBag::class, $newBag);
        $this->assertNotSame($newBag, $bag);
        $this->assertSame([], $this->propRefl->getValue($bag));
        $this->assertSame(['username' => $property], $this->propRefl->getValue($newBag));
    }
}
