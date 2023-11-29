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
 * @covers \Nelmio\Alice\Definition\Value\FixtureMatchReferenceValue
 * @internal
 */
class FixtureMatchReferenceValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(FixtureMatchReferenceValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $regex = '/dummy/';
        $value = new FixtureMatchReferenceValue($regex);

        self::assertEquals($regex, $value->getValue());
    }

    public function testCanMatchAgainstValues(): void
    {
        $regex = '/^d/';
        $value = new FixtureMatchReferenceValue($regex);

        self::assertTrue($value->match('d'));
        self::assertFalse($value->match('a'));
    }

    public function testCanCreateAReferenceForWildcards(): void
    {
        $expected = new FixtureMatchReferenceValue('/^user.*/');
        $actual = FixtureMatchReferenceValue::createWildcardReference('user');

        self::assertEquals($expected, $actual);
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        self::assertEquals('@(regex: /^dummy.*/)', (string) $value);
    }

    public function testReferenceIsRegexEscaped(): void
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('du/m*m+y.ref[ere]n(c)e');
        self::assertEquals('/^du\\/m\\*m\\+y\\.ref\\[ere\\]n\\(c\\)e.*/', $value->getValue());
    }
}
