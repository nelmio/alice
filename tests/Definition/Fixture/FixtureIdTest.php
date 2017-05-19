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
/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\FixtureIdInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Fixture\FixtureId
 */
class FixtureIdTest extends TestCase
{
    public function testIsAFixtureId()
    {
        $this->assertTrue(is_a(FixtureId::class, FixtureIdInterface::class, true));
    }

    public function testAccessor()
    {
        $id = new FixtureId('foo');
        $this->assertEquals('foo', $id->getId());
    }
}
