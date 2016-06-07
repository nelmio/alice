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
    /**
     * @dataProvider provideValues
     */
    public function testAccessors($value)
    {
        $property = 'username';

        $definition = new Property($property, $value);

        $this->assertEquals($property, $definition->getName());
        $this->assertEquals($value, $definition->getValue());
    }

    public function testIsImmutable()
    {
        $property = 'username';
        $value = new \stdClass();

        $definition = new Property($property, $value);

        $this->assertNotSame($definition->getValue(), $definition->getValue());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $definition = new Property('username', null);
        clone $definition;
    }

    public function provideValues()
    {
        yield 'null value' => [null];
        yield 'string value' => ['azerty'];
        yield 'object value' => [new \stdClass()];
    }
}
