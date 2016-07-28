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

use Nelmio\Alice\Definition\ValueInterface;

/**
 * @covers Nelmio\Alice\Definition\Value\ListValue
 */
class ListValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(ListValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $list = [];
        $value = new ListValue($list);

        $this->assertEquals($list, $value->getValue());

        $list = [new \stdClass()];
        $value = new ListValue($list);

        $this->assertEquals($list, $value->getValue());
    }

    public function testIsImmutable()
    {
        $value = new ListValue([
            $arg0 = new \stdClass(),
        ]);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $value->getValue()[0]->foo = 'baz';

        $this->assertEquals(
            [
                new \stdClass(),
            ],
            $value->getValue()
        );
    }
}
