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
 * @covers \Nelmio\Alice\Definition\Value\FixturePropertyValue
 */
class FixturePropertyValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        static::assertTrue(is_a(FixturePropertyValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = new FakeValue();
        $property = 'username';

        $value = new FixturePropertyValue($reference, $property);

        static::assertEquals($reference, $value->getReference());
        static::assertEquals($property, $value->getProperty());
        static::assertEquals([$reference, $property], $value->getValue());
    }

    /**
     * @depends \Nelmio\Alice\Definition\Value\FixtureReferenceValueTest::testIsImmutable
     */
    public function testIsImmutable(): void
    {
        static::assertTrue(true, 'Nothing to do.');
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new FixturePropertyValue(
            new FixtureReferenceValue('dummy'),
            'foo'
        );

        static::assertEquals('@dummy->foo', (string) $value);
    }
}
