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
 * @covers Nelmio\Alice\Definition\Value\FixtureMethodCallValue
 */
class FixtureMethodCallValueTest extends \PHPUnit_Framework_TestCase
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
}
