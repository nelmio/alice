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

use Nelmio\Alice\Entity\StdClassFactory;

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

    public function testIsMutable()
    {
        $value = new \stdClass();
        $definition = new Property('username', $value);

        // Mutate injected value
        $value->foo = 'bar';

        // Mutate returned value
        $definition->getValue()->ping = 'pong';

        $expected = StdClassFactory::create(['foo' => 'bar', 'ping' => 'pong']);
        $actual = $definition->getValue();

        $this->assertEquals($expected, $actual);
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $name = 'username';
        $definition = new Property($name, 'foo');
        $newDefinition = $definition->withValue(new \stdClass());

        $this->assertEquals(
            new Property($name, 'foo'),
            $definition
        );
        $this->assertEquals(
            new Property($name, new \stdClass()),
            $newDefinition
        );
    }
}
