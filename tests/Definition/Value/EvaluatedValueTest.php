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
 * @internal
 */
class EvaluatedValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(EvaluatedValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $expression = '"Hello"." "."world!"';
        $value = new EvaluatedValue($expression);

        self::assertEquals($expression, $value->getValue());
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new EvaluatedValue('"Hello"." "."world!"');

        self::assertEquals('"Hello"." "."world!"', $value);
    }
}
