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
 * @covers \Nelmio\Alice\Definition\Value\EvaluatedValue
 */
class EvaluatedValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(EvaluatedValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $expression = '"Hello"." "."world!"';
        $value = new EvaluatedValue($expression);

        $this->assertEquals($expression, $value->getValue());
    }

    public function testIsCastableIntoAString()
    {
        $value = new EvaluatedValue('"Hello"." "."world!"');

        $this->assertEquals('"Hello"." "."world!"', $value);
    }
}
