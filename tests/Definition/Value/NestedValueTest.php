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
 * @covers \Nelmio\Alice\Definition\Value\NestedValue
 */
class NestedValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(NestedValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $list = [];
        $value = new NestedValue($list);

        $this->assertEquals($list, $value->getValue());

        $list = [new \stdClass()];
        $value = new NestedValue($list);

        $this->assertEquals($list, $value->getValue());
    }

    public function testIsNotImmutable()
    {
        $value = new NestedValue([
            $arg0 = new \stdClass(),
        ]);

        $this->assertSame($arg0, $value->getValue()[0]);
    }

    public function testCanBeCastedIntoAString()
    {
        $this->assertEquals("(nested) array (\n)", (string) (new NestedValue([])));
    }
}
