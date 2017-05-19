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
 * @covers \Nelmio\Alice\Definition\Value\ValueForCurrentValue
 */
class ValueForCurrentValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(ValueForCurrentValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $value = new ValueForCurrentValue();
        $this->assertEquals('current', $value->getValue());
    }

    public function testCanBeCastedIntoAString()
    {
        $value = new ValueForCurrentValue();
        $this->assertEquals('current', $value);
    }
}
