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
 * @covers Nelmio\Alice\Fixture\PropertyDefinition
 */
class PropertyDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $name = 'username';
        $value = [
            'hello',
            new \stdClass(),
        ];
        $requiresUnique = true;
        
        $definition = new PropertyDefinition($name, $value, $requiresUnique);
        
        $this->assertEquals($name, $definition->getName());
        $this->assertSame($value, $definition->getValue());
        $this->assertEquals($requiresUnique, $definition->requiresUnique());
    }

    public function testGetValueImmutability()
    {
        $object = new \stdClass();
        $definition = new PropertyDefinition('username', $object);

        $this->assertEquals($object, $definition->getValue());
        $this->assertNotSame($definition->getValue(), $definition->getValue());

        $definition = new PropertyDefinition('username', 'scalar');

        $this->assertEquals('scalar', $definition->getValue());
    }
}
