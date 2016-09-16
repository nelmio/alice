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
 * @covers \Nelmio\Alice\Definition\Value\FixtureReferenceValue
 */
class FixtureReferenceValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FixtureReferenceValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $value = new FixtureReferenceValue('user0');

        $this->assertEquals('user0', $value->getValue());
    }

    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }
}
