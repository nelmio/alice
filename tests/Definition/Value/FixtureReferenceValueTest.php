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
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\Value\FixtureReferenceValue
 */
class FixtureReferenceValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        static::assertTrue(is_a(FixtureReferenceValue::class, ValueInterface::class, true));
    }

    public function testCanBeInstantiatedWithOnlyAStringOrAValue(): void
    {
        new FixtureReferenceValue('user0');
        new FixtureReferenceValue(new FakeValue());

        try {
            new FixtureReferenceValue(null);
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "null" instead.',
                $exception->getMessage()
            );
        }

        try {
            new FixtureReferenceValue(true);
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "boolean" instead.',
                $exception->getMessage()
            );
        }

        try {
            new FixtureReferenceValue(10);
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "integer" instead.',
                $exception->getMessage()
            );
        }

        try {
            new FixtureReferenceValue(.5);
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "double" instead.',
                $exception->getMessage()
            );
        }

        try {
            new FixtureReferenceValue([]);
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "array" instead.',
                $exception->getMessage()
            );
        }

        try {
            new FixtureReferenceValue(new stdClass());
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "stdClass" instead.',
                $exception->getMessage()
            );
        }

        try {
            new FixtureReferenceValue(function (): void {
            });
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Expected reference to be either a string or a "Nelmio\Alice\Definition\ValueInterface" instance, got'
                .' "Closure" instead.',
                $exception->getMessage()
            );
        }
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $value = new FixtureReferenceValue('user0');

        static::assertEquals('user0', $value->getValue());
    }

    public function testIsImmutable(): void
    {
        static::assertTrue(true, 'Nothing to do.');
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new FixtureReferenceValue('');
        static::assertEquals('@', (string) $value);

        $value = new FixtureReferenceValue('user0');
        static::assertEquals('@user0', (string) $value);
    }
}
