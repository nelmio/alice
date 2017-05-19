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
 * @covers \Nelmio\Alice\Definition\Value\EvaluatedValue
 */
class EvaluatedValueTest extends TestCase
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

    public function testCanBeCastedIntoAString()
    {
        $value = new EvaluatedValue('"Hello"." "."world!"');

        $this->assertEquals('"Hello"." "."world!"', $value);
    }
}
