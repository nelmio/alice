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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\UniqueValuesPool
 * @internal
 */
class UniqueValuesPoolTest extends TestCase
{
    public function testDoesNotHaveValueIfValueIsNotCached(): void
    {
        $pool = new UniqueValuesPool();
        self::assertFalse($pool->has(new UniqueValue('', '')));
    }

    /**
     * @depends \Nelmio\Alice\Definition\Value\UniqueValueTest::testIsImmutable
     */
    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }

    /**
     * @dataProvider provideHasValueSet
     */
    public function testHasObjectValue(UniqueValuesPool $pool, UniqueValue $value, bool $expected): void
    {
        self::assertEquals($expected, $pool->has($value));

        $pool->add($value);
        self::assertTrue($pool->has($value));
    }

    public static function provideHasValueSet(): iterable
    {
        $baseValue = new UniqueValue('foo', 'temporary value');

        // Checks for null
        yield '[null value] empty' => [
            new UniqueValuesPool(),
            $baseValue->withValue(null),
            false,
        ];

        yield '[null value] with `null' => [
            self::createPoolWithValue(null),
            $baseValue->withValue(null),
            true,
        ];

        yield '[null value] with `false' => [
            self::createPoolWithValue(false),
            $baseValue->withValue(null),
            false,
        ];

        yield '[null value] with empty array' => [
            self::createPoolWithValue([]),
            $baseValue->withValue(null),
            false,
        ];

        yield '[null value] with empty string' => [
            self::createPoolWithValue(''),
            $baseValue->withValue(null),
            false,
        ];

        // Full checks for a scalar value
        yield '[`true`] empty' => [
            new UniqueValuesPool(),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `true`' => [
            self::createPoolWithValue(true),
            $baseValue->withValue(true),
            true,
        ];

        yield '[`true`] with `1`' => [
            self::createPoolWithValue(1),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `-1`' => [
            self::createPoolWithValue(-1),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `"1"`' => [
            self::createPoolWithValue('1'),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `"-1"`' => [
            self::createPoolWithValue('-1'),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `"alice"`' => [
            self::createPoolWithValue('alice'),
            $baseValue->withValue(true),
            false,
        ];

        // Check objects
        yield 'with two equivalent objects' => [
            self::createPoolWithValue(new stdClass()),
            $baseValue->withValue(new stdClass()),
            true,
        ];

        yield 'with two non-equivalent objects' => [
            self::createPoolWithValue(new stdClass()),
            $baseValue->withValue(StdClassFactory::create(['foo' => 'bar'])),
            false,
        ];

        yield 'with two equivalent objects (2)' => [
            self::createPoolWithValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ]),
            ),
            $baseValue->withValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ]),
            ),
            true,
        ];

        yield 'with two non-equivalent objects (2)' => [
            self::createPoolWithValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ]),
            ),
            $baseValue->withValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => new stdClass(),
                    ]),
                ]),
            ),
            false,
        ];

        // Checks arrays
        yield 'two identical arrays' => [
            self::createPoolWithValue([]),
            $baseValue->withValue([]),
            true,
        ];

        yield 'two equivalent arrays' => [
            self::createPoolWithValue([10, 20]),
            $baseValue->withValue([20, 10]),
            true,
        ];

        yield 'two equivalent arrays (2)' => [
            self::createPoolWithValue([10, 'foo' => new stdClass(), 20]),
            $baseValue->withValue([20, 10, 'foo' => new stdClass()]),
            true,
        ];

        yield 'two non-equivalent arrays (2)' => [
            self::createPoolWithValue([10, 20, 30]),
            $baseValue->withValue([20, 10]),
            false,
        ];

        yield 'two non-equivalent arrays (3)' => [
            self::createPoolWithValue([1]),
            $baseValue->withValue([true]),
            false,
        ];

        yield 'two non-equivalent arrays (4)' => [
            self::createPoolWithValue([10, 'foo' => StdClassFactory::create(['foo' => 'bar']), 20]),
            $baseValue->withValue([20, 10, 'foo' => new stdClass()]),
            false,
        ];
    }

    private static function createPoolWithValue($value): UniqueValuesPool
    {
        $pool = new UniqueValuesPool();
        $pool->add(new UniqueValue('foo', $value));

        return $pool;
    }
}
