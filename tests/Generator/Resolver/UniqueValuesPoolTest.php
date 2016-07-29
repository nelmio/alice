<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;

/**
 * @covers Nelmio\Alice\Generator\Resolver\UniqueValuesPool
 */
class UniqueValuesPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNotHaveValueIfValueIsNotCached()
    {
        $pool = new UniqueValuesPool();
        $this->assertFalse($pool->has(new UniqueValue('', '')));
    }

    public function testHasScalarValue()
    {
        $pool = new UniqueValuesPool();
        $value0 = new UniqueValue('scalar', 100);
        $value1 = new UniqueValue('scalar', 101);
        $pool->add($value0);

        $this->assertTrue($pool->has($value0));
        $this->assertFalse($pool->has($value1));

        $pool->add($value1);

        $this->assertTrue($pool->has($value0));
        $this->assertTrue($pool->has($value1));

        $this->assertFalse($pool->has($value0->withValue(200)));
    }

    public function testHasEmptyArrayValue()
    {
        $pool = new UniqueValuesPool();
        $value = new UniqueValue('empty array', []);
        $pool->add($value);

        $this->assertTrue($pool->has($value));
    }

    public function testHasArrayWithScalarValue()
    {
        $pool = new UniqueValuesPool();
        $value = new UniqueValue('scalar array', [10, 11]);
        $pool->add($value);

        $this->assertTrue($pool->has($value));

        $newValue = [11, 10];
        $this->assertFalse($pool->has($value->withValue($newValue)));
    }

    public function testHasArrayWithObjectValue()
    {
        $pool = new UniqueValuesPool();
        $value = new UniqueValue('object array', [new \stdClass()]);
        $pool->add($value);

        $this->assertTrue($pool->has($value));

        $newValue = new \stdClass();
        $newValue->foo = 'bar';
        $newValue = $value->withValue([$newValue]);

        $this->assertFalse($pool->has($newValue));
    }

    public function testHasObjectValue()
    {
        $pool = new UniqueValuesPool();
        $value = new UniqueValue('object', new \stdClass());
        $pool->add($value);

        $this->assertTrue($pool->has($value));

        $newValue = $value->getValue();
        $this->assertTrue($pool->has($value->withValue($newValue)));

        $newValue->foo = 'bar';
        $this->assertFalse($pool->has($value->withValue($newValue)));
    }

    /**
     * @depends Nelmio\Alice\Definition\Value\UniqueValueTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }
}
