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
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Flag\ConfiguratorFlag
 */
class ConfiguratorFlagTest extends TestCase
{
    public function testIsAFlag()
    {
        $this->assertTrue(is_a(ConfiguratorFlag::class, FlagInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $flag = new ConfiguratorFlag();

        $this->assertEquals('configurator', $flag->__toString());
    }
}
