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

use InvalidArgumentException;
use Nelmio\Alice\Definition\ValueInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(FixtureReferenceValue::class)]
final class FixtureReferenceValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(FixtureReferenceValue::class, ValueInterface::class, true));
    }

    public function testCanBeInstantiatedWithOnlyAStringOrAValue(): void
    {
        new FixtureReferenceValue('user0');
        new FixtureReferenceValue(new FakeValue());

        try {
            new FixtureReferenceValue(null);
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "null" instead.',
                $exception->getMessage(),
            );
        }

        try {
            new FixtureReferenceValue(true);
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "boolean" instead.',
                $exception->getMessage(),
            );
        }

        try {
            new FixtureReferenceValue(10);
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "integer" instead.',
                $exception->getMessage(),
            );
        }

        try {
            new FixtureReferenceValue(.5);
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "double" instead.',
                $exception->getMessage(),
            );
        }

        try {
            new FixtureReferenceValue([]);
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "array" instead.',
                $exception->getMessage(),
            );
        }

        try {
            new FixtureReferenceValue(new stdClass());
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "stdClass" instead.',
                $exception->getMessage(),
            );
        }

        try {
            new FixtureReferenceValue(
                static function (): void {
                },
            );
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "Closure" instead.',
                $exception->getMessage(),
            );
        }
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $value = new FixtureReferenceValue('user0');

        self::assertEquals('user0', $value->getValue());
    }

    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new FixtureReferenceValue('');
        self::assertEquals('@', (string) $value);

        $value = new FixtureReferenceValue('user0');
        self::assertEquals('@user0', (string) $value);
    }
}
