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

use InvalidArgumentException;
use Nelmio\Alice\Definition\ValueInterface;
use const PHP_VERSION_ID;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\Value\UniqueValue
 */
class UniqueValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        static::assertTrue(is_a(UniqueValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideValues
     */
    public function testReadAccessorsReturnPropertiesValues($value): void
    {
        $id = 'Nelmio\Entity\User#user0#username';

        $definition = new UniqueValue($id, $value);

        static::assertEquals($id, $definition->getId());
        static::assertEquals($value, $definition->getValue());
    }

    public function testCannotCreateUniqueOfUniqueValue(): void
    {
        $definition = new UniqueValue('', new stdClass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a unique value of a unique value for value "".');

        new UniqueValue('', $definition);
    }

    public function testIsImmutable(): void
    {
        $id = 'Nelmio\Entity\User#user0#username';
        $value = [
            $arg0 = new stdClass()
        ];
        $definition = new UniqueValue($id, $value);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $definition->getValue()[0]->foo = 'baz';

        static::assertEquals([new stdClass()], $definition->getValue());
    }

    public function testImmutableFactories(): void
    {
        $id = 'Nelmio\Entity\User#user0#username';
        $value = new stdClass();
        $newValue = new stdClass();
        $newValue->foo = 'bar';

        $original = new UniqueValue($id, $value);
        $clone = $original->withValue($newValue);

        static::assertInstanceOf(UniqueValue::class, $clone);
        static::assertEquals($id, $original->getId());
        static::assertEquals($id, $clone->getId());
        static::assertEquals($value, $original->getValue());
        static::assertEquals($newValue, $clone->getValue());
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new UniqueValue('', 'foo');
        static::assertEquals('(unique) \'foo\'', (string) $value);

        $value = new UniqueValue('', new stdClass());

        if (PHP_VERSION_ID >= 70300) {
            $expectedStdClass = "(unique) (object) array(\n)";
        } else {
            $expectedStdClass = "(unique) stdClass::__set_state(array(\n))";
        }
        static::assertEquals($expectedStdClass, (string) $value);

        $value = new UniqueValue('', new DummyValue('foo'));
        static::assertEquals('(unique) foo', (string) $value);
    }

    public function provideValues()
    {
        yield 'null value' => [null];
        yield 'string value' => ['azerty'];
        yield 'object value' => [new stdClass()];
    }
}
