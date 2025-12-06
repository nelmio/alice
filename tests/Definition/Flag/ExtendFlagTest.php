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

use Nelmio\Alice\Definition\FlagInterface;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Flag\ExtendFlag
 * @internal
 */
final class ExtendFlagTest extends TestCase
{
    public function testIsAFlag(): void
    {
        self::assertTrue(is_a(ExtendFlag::class, FlagInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = new FixtureReference('Nelmio\Alice\EntityUser#user_base');
        $flag = new ExtendFlag($reference);

        self::assertEquals($reference, $flag->getExtendedFixture());
        self::assertEquals('extends Nelmio\Alice\EntityUser#user_base', $flag->__toString());
    }
}
