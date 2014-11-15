<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

class PropertyDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testWillParseFlagsOutOfName()
    {
        $definition = new PropertyDefinition('username (unique)', '<username()>');

        $this->assertEquals('username', $definition->getName());
    }

    public function testWillRegisterNameFlags()
    {
        $definition = new PropertyDefinition('username (unique)', '<username()>');

        $this->assertEquals(['unique' => true], $definition->getNameFlags());
    }

    public function testRequiresUniqueWillReturnIfThePropertyIsFlaggedAsUnique()
    {
        $nonuniqueDefinition = new PropertyDefinition('username', '<username()>');
        $uniqueDefinition = new PropertyDefinition('username (unique)', '<username()>');

        $this->assertFalse($nonuniqueDefinition->requiresUnique());
        $this->assertTrue($uniqueDefinition->requiresUnique());
    }

    public function testIsBasicWillReturnFalseIfThePropertyIsAConstructor()
    {
        $definition = new PropertyDefinition('__construct', ['1', '2']);

        $this->assertFalse($definition->isBasic());
    }

    public function testIsBasicWillReturnFalseIfThePropertyIsASetter()
    {
        $definition = new PropertyDefinition('__set', 'setterFunc');

        $this->assertFalse($definition->isBasic());
    }

    public function testIsBasicWillReturnTrueIfTheDefinitionRepresentsAValue()
    {
        $definition = new PropertyDefinition('username', '<username()>');

        $this->assertTrue($definition->isBasic());
    }

    public function testIsConstructorWillReturnIfTheDefinitionIsTheConstructor()
    {
        $constructorDef = new PropertyDefinition('__construct', ['1', '2']);
        $normalDef = new PropertyDefinition('username', '<username()>');

        $this->assertTrue($constructorDef->isConstructor());
        $this->assertFalse($normalDef->isConstructor());
    }

    public function testIsCustomSetterWillReturnIfTheDefinitionIsACustomSetter()
    {
        $setterDef = new PropertyDefinition('__set', 'setterFunc');
        $normalDef = new PropertyDefinition('username', '<username()>');

        $this->assertTrue($setterDef->isCustomSetter());
        $this->assertFalse($normalDef->isCustomSetter());
    }
}
