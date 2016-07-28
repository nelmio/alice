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
 * @covers Nelmio\Alice\Definition\Value\OptionalValue
 */
class OptionalValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(OptionalValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $quantifier = 50;
        $firstMember = 'first';
        $secondMember = 'second';

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        $this->assertEquals($quantifier, $value->getQuantifier());
        $this->assertEquals($firstMember, $value->getFirstMember());
        $this->assertEquals($secondMember, $value->getSecondMember());
        $this->assertEquals([$quantifier, $firstMember, $secondMember], $value->getValue());

        $quantifier = new \stdClass();
        $firstMember = new \stdClass();
        $secondMember = new \stdClass();

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        $this->assertEquals($quantifier, $value->getQuantifier());
        $this->assertEquals($firstMember, $value->getFirstMember());
        $this->assertEquals($secondMember, $value->getSecondMember());
        $this->assertEquals([$quantifier, $firstMember, $secondMember], $value->getValue());
    }

    public function testIsImmutable()
    {
        $quantifier = new \stdClass();
        $firstMember = new \stdClass();
        $secondMember = new \stdClass();

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        $this->assertNotSame($value->getQuantifier(), $value->getQuantifier());
        $this->assertNotSame($value->getFirstMember(), $value->getFirstMember());
        $this->assertNotSame($value->getSecondMember(), $value->getSecondMember());
        $this->assertNotSame($value->getValue(), $value->getValue());
    }

    public function testIsDeepClonable()
    {
        $reflClass = new \ReflectionClass(OptionalValue::class);
        $quantifierRefl = $reflClass->getProperty('quantifier');
        $quantifierRefl->setAccessible(true);
        $firstMemberRelf = $reflClass->getProperty('firstMember');
        $firstMemberRelf->setAccessible(true);
        $secondMemberRefl = $reflClass->getProperty('secondMember');
        $secondMemberRefl->setAccessible(true);

        $quantifier = 50;
        $firstMember = 'first';
        $secondMember = 'second';

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);
        $clone = clone $value;

        $this->assertEquals($clone, $value);
        $this->assertNotSame($clone, $value);

        $quantifier = new \stdClass();
        $firstMember = new \stdClass();
        $secondMember = new \stdClass();

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);
        $clone = clone $value;

        $this->assertEquals($clone, $value);
        $this->assertNotSame($clone, $value);

        $this->assertNotSame($quantifierRefl->getValue($value), $quantifierRefl->getValue($clone));
        $this->assertNotSame($firstMemberRelf->getValue($value), $firstMemberRelf->getValue($clone));
        $this->assertNotSame($secondMemberRefl->getValue($value), $secondMemberRefl->getValue($clone));
    }
}
