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

/**
 * @covers \Nelmio\Alice\Generator\Resolver\UniqueValuesPool
 */
class UniqueValuesPoolTest extends TestCase
{
    public function testDoesNotHaveValueIfValueIsNotCached()
    {
        $pool = new UniqueValuesPool();
        $this->assertFalse($pool->has(new UniqueValue('', '')));
    }

    /**
     * @depends Nelmio\Alice\Definition\Value\UniqueValueTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    /**
     * @dataProvider provideHasValueSet
     */
    public function testHasObjectValue(UniqueValuesPool $pool, UniqueValue $value, bool $expected)
    {
        $this->assertEquals($expected, $pool->has($value));

        $pool->add($value);
        $this->assertTrue($pool->has($value));
    }

    public function provideHasValueSet()
    {
        $baseValue = new UniqueValue('foo', 'temporary value');

        // Checks for null
        yield '[null value] empty' => [
            new UniqueValuesPool(),
            $baseValue->withValue(null),
            false,
        ];

        yield '[null value] with `null' => [
            $this->createPoolWithValue(null),
            $baseValue->withValue(null),
            true,
        ];

        yield '[null value] with `false' => [
            $this->createPoolWithValue(false),
            $baseValue->withValue(null),
            false,
        ];

        yield '[null value] with empty array' => [
            $this->createPoolWithValue([]),
            $baseValue->withValue(null),
            false,
        ];

        yield '[null value] with empty string' => [
            $this->createPoolWithValue(''),
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
            $this->createPoolWithValue(true),
            $baseValue->withValue(true),
            true,
        ];

        yield '[`true`] with `1`' => [
            $this->createPoolWithValue(1),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `-1`' => [
            $this->createPoolWithValue(-1),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `"1"`' => [
            $this->createPoolWithValue('1'),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `"-1"`' => [
            $this->createPoolWithValue('-1'),
            $baseValue->withValue(true),
            false,
        ];

        yield '[`true`] with `"alice"`' => [
            $this->createPoolWithValue('alice'),
            $baseValue->withValue(true),
            false,
        ];


        // Check objects
        yield 'with two equivalent objects' => [
            $this->createPoolWithValue(new \stdClass()),
            $baseValue->withValue(new \stdClass()),
            true,
        ];

        yield 'with two non-equivalent objects' => [
            $this->createPoolWithValue(new \stdClass()),
            $baseValue->withValue(StdClassFactory::create(['foo' => 'bar'])),
            false,
        ];

        yield 'with two equivalent objects (2)' => [
            $this->createPoolWithValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ])
                ])
            ),
            $baseValue->withValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ])
                ])
            ),
            true,
        ];

        yield 'with two non-equivalent objects (2)' => [
            $this->createPoolWithValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ])
                ])
            ),
            $baseValue->withValue(
                StdClassFactory::create([
                    'relatedDummy' => StdClassFactory::create([
                        'foo' => new \stdClass(),
                    ])
                ])
            ),
            false,
        ];


        // Checks arrays
        yield 'two identical arrays' => [
            $this->createPoolWithValue([]),
            $baseValue->withValue([]),
            true,
        ];

        yield 'two equivalent arrays' => [
            $this->createPoolWithValue([10, 20]),
            $baseValue->withValue([20, 10]),
            true,
        ];

        yield 'two equivalent arrays (2)' => [
            $this->createPoolWithValue([10, 'foo' => new \stdClass(), 20]),
            $baseValue->withValue([20, 10, 'foo' => new \stdClass()]),
            true,
        ];

        yield 'two non-equivalent arrays (2)' => [
            $this->createPoolWithValue([10, 20, 30]),
            $baseValue->withValue([20, 10]),
            false,
        ];

        yield 'two non-equivalent arrays (3)' => [
            $this->createPoolWithValue([1]),
            $baseValue->withValue([true]),
            false,
        ];

        yield 'two non-equivalent arrays (4)' => [
            $this->createPoolWithValue([10, 'foo' => StdClassFactory::create(['foo' => 'bar']), 20]),
            $baseValue->withValue([20, 10, 'foo' => new \stdClass()]),
            false,
        ];
    }

    private function createPoolWithValue($value): UniqueValuesPool
    {
        $pool = new UniqueValuesPool();
        $pool->add(new UniqueValue('foo', $value));

        return $pool;
    }
}
