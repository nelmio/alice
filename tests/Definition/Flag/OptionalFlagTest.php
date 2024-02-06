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

namespace Nelmio\Alice\Definition\Flag;

use InvalidArgumentException;
use Nelmio\Alice\Definition\FlagInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Flag\OptionalFlag
 * @internal
 */
class OptionalFlagTest extends TestCase
{
    public function testIsAFlag(): void
    {
        self::assertTrue(is_a(OptionalFlag::class, FlagInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $flag = new OptionalFlag(50);

        self::assertEquals(50, $flag->getPercentage());
        self::assertEquals('%?', $flag->__toString());
    }

    /**
     * @dataProvider providePercentageValues
     */
    public function testThrowsExceptionIfPercentageValueIsInvalid(int $percentage, ?string $expectedMessage = null): void
    {
        try {
            new OptionalFlag($percentage);
            if (null !== $expectedMessage) {
                self::fail('Expected exception to be thrown.');
            }
        } catch (InvalidArgumentException $exception) {
            if (null === $expectedMessage) {
                self::fail('Was not expecting exception to be thrown.');
            }

            self::assertEquals($expectedMessage, $exception->getMessage());
        }
    }

    public static function providePercentageValues(): iterable
    {
        yield 'negative value' => [
            -1,
            'Expected optional flag to be an integer element of [0;100]. Got "-1" instead.',
        ];
        yield 'lower border (in)' => [
            0,
            null,
        ];
        yield 'upper border (in)' => [
            100,
            null,
        ];
        yield 'upper border (out)' => [
            101,
            'Expected optional flag to be an integer element of [0;100]. Got "101" instead.',
        ];
    }
}
