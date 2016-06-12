<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

/**
 * @covers Nelmio\Alice\Definition\Value\UniqueValue
 */
class UniqueValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideValues
     */
    public function testAccessors($value)
    {
        $id = 'Nelmio\Entity\User#user0#username';

        $definition = new UniqueValue($id, $value);

        $this->assertEquals($id, $definition->getId());
        $this->assertEquals($value, $definition->getValue());
    }

    public function testIsImmutable()
    {
        $id = 'Nelmio\Entity\User#user0#username';
        $value = new \stdClass();

        $definition = new UniqueValue($id, $value);

        $this->assertNotSame($definition->getValue(), $definition->getValue());
    }

    public function testIsDeepClonable()
    {
        $definition = new UniqueValue('dummy', null);
        $clone = clone $definition;
        $this->assertEquals($clone, $definition);
        $this->assertNotSame($clone, $definition);

        $value = new \stdClass();
        $definition = new UniqueValue('dummy', $value);
        $clone = clone $definition;
        $this->assertEquals($clone, $definition);
        $this->assertNotSame($clone->getValue(), $value);
    }

    public function provideValues()
    {
        yield 'null value' => [null];
        yield 'string value' => ['azerty'];
        yield 'object value' => [new \stdClass()];
    }
}
