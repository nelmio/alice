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
use PHPUnit\Framework\TestCase;
use stdClass;
use const PHP_VERSION_ID;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(UniqueValue::class)]
final class UniqueValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(UniqueValue::class, ValueInterface::class, true));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideValues')]
    public function testReadAccessorsReturnPropertiesValues($value): void
    {
        $id = 'Nelmio\Entity\User#user0#username';

        $definition = new UniqueValue($id, $value);

        self::assertEquals($id, $definition->getId());
        self::assertEquals($value, $definition->getValue());
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
            $arg0 = new stdClass(),
        ];
        $definition = new UniqueValue($id, $value);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $definition->getValue()[0]->foo = 'baz';

        self::assertEquals([new stdClass()], $definition->getValue());
    }

    public function testImmutableFactories(): void
    {
        $id = 'Nelmio\Entity\User#user0#username';
        $value = new stdClass();
        $newValue = new stdClass();
        $newValue->foo = 'bar';

        $original = new UniqueValue($id, $value);
        $clone = $original->withValue($newValue);

        self::assertInstanceOf(UniqueValue::class, $clone);
        self::assertEquals($id, $original->getId());
        self::assertEquals($id, $clone->getId());
        self::assertEquals($value, $original->getValue());
        self::assertEquals($newValue, $clone->getValue());
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new UniqueValue('', 'foo');
        self::assertEquals('(unique) \'foo\'', (string) $value);

        $value = new UniqueValue('', new stdClass());

        if (PHP_VERSION_ID >= 70300) {
            $expectedStdClass = "(unique) (object) array(\n)";
        } else {
            $expectedStdClass = "(unique) stdClass::__set_state(array(\n))";
        }
        self::assertEquals($expectedStdClass, (string) $value);

        $value = new UniqueValue('', new DummyValue('foo'));
        self::assertEquals('(unique) foo', (string) $value);
    }

    public static function provideValues(): iterable
    {
        yield 'null value' => [null];
        yield 'string value' => ['azerty'];
        yield 'object value' => [new stdClass()];
    }
}
