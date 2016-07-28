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
 * @covers Nelmio\Alice\Definition\Property
 */
class PropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $property = 'username';
        $value = new \stdClass();
        $definition = new Property($property, $value);

        $this->assertEquals($property, $definition->getName());
        $this->assertEquals($value, $definition->getValue());
    }

    public function testIsImmutable()
    {
        $value = [
            $arg0 = new \stdClass(),
        ];
        $definition = new Property('username', $value);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $definition->getValue()[0]->foo = 'baz';

        $this->assertEquals([new \stdClass()], $definition->getValue());
    }

    public function testWithersAreImmutablesAndReturnNewModifiedInstance()
    {
        $property = 'username';
        $value = 'foo';
        $newValue = [
            $arg0 = new \stdClass(),
        ];
        $definition = new Property($property, $value);
        $newDefinition = $definition->withValue($newValue);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $newDefinition->getValue()[0]->foo = 'baz';

        $this->assertInstanceOf(Property::class, $newDefinition);

        $this->assertEquals($property, $definition->getName());
        $this->assertEquals($property, $newDefinition->getName());

        $this->assertEquals($value, $definition->getValue());
        $this->assertEquals([new \stdClass()], $newDefinition->getValue());
    }
}
