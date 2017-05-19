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
 * @covers \Nelmio\Alice\Definition\Value\FixtureMethodCallValue
 */
class FixtureMethodCallValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FixtureMethodCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = new FakeValue();
        $function = new FunctionCallValue('getName');

        $value = new FixtureMethodCallValue($reference, $function);

        $this->assertEquals($reference, $value->getReference());
        $this->assertEquals($function, $value->getFunctionCall());
        $this->assertEquals([$reference, $function], $value->getValue());
    }

    /**
     * @depends Nelmio\Alice\Definition\ServiceReference\FixtureReferenceTest::testIsImmutable
     * @depends Nelmio\Alice\Definition\Value\FunctionCallValueTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    public function testCanBeCastedIntoAString()
    {
        $value = new FixtureMethodCallValue(
            new FixtureReferenceValue('dummy'),
            new FunctionCallValue('foo')
        );
        $this->assertEquals('@dummy->foo()', (string) $value);

        $value = new FixtureMethodCallValue(
            new FixtureReferenceValue('dummy'),
            new FunctionCallValue('foo', ['bar'])
        );
        $this->assertEquals("@dummy->foo(array (\n  0 => 'bar',\n))", (string) $value);
    }
}
