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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FixturePropertyValue::class)]
final class FixturePropertyValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(FixturePropertyValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = new FakeValue();
        $property = 'username';

        $value = new FixturePropertyValue($reference, $property);

        self::assertEquals($reference, $value->getReference());
        self::assertEquals($property, $value->getProperty());
        self::assertEquals([$reference, $property], $value->getValue());
    }

    #[DependsExternal(FixtureReferenceValueTest::class, 'testIsImmutable')]
    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new FixturePropertyValue(
            new FixtureReferenceValue('dummy'),
            'foo',
        );

        self::assertEquals('@dummy->foo', (string) $value);
    }
}
