<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Value\UniqueValue
 */
class UniqueValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(UniqueValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideValues
     */
    public function testReadAccessorsReturnPropertiesValues($value)
    {
        $id = 'Nelmio\Entity\User#user0#username';

        $definition = new UniqueValue($id, $value);

        $this->assertEquals($id, $definition->getId());
        $this->assertEquals($value, $definition->getValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot create a unique value of a unique value for value "".
     */
    public function testCannotCreateUniqueOfUniqueValue()
    {
        $definition = new UniqueValue('', new \stdClass());
        new UniqueValue('', $definition);
    }

    public function testIsImmutable()
    {
        $id = 'Nelmio\Entity\User#user0#username';
        $value = [
            $arg0 = new \stdClass()
        ];
        $definition = new UniqueValue($id, $value);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $definition->getValue()[0]->foo = 'baz';

        $this->assertEquals([new \stdClass()], $definition->getValue());
    }

    public function testImmutableFactories()
    {
        $id = 'Nelmio\Entity\User#user0#username';
        $value = new \stdClass();
        $newValue = new \stdClass();
        $newValue->foo = 'bar';

        $original = new UniqueValue($id, $value);
        $clone = $original->withValue($newValue);

        $this->assertInstanceOf(UniqueValue::class, $clone);
        $this->assertEquals($id, $original->getId());
        $this->assertEquals($id, $clone->getId());
        $this->assertEquals($value, $original->getValue());
        $this->assertEquals($newValue, $clone->getValue());
    }

    public function testCanBeCastedIntoAString()
    {
        $value = new UniqueValue('', 'foo');
        $this->assertEquals('(unique) \'foo\'', (string) $value);

        $value = new UniqueValue('', new \stdClass());
        $this->assertEquals("(unique) stdClass::__set_state(array(\n))", (string) $value);

        $value = new UniqueValue('', new DummyValue('foo'));
        $this->assertEquals('(unique) foo', (string) $value);
    }

    public function provideValues()
    {
        yield 'null value' => [null];
        yield 'string value' => ['azerty'];
        yield 'object value' => [new \stdClass()];
    }
}
