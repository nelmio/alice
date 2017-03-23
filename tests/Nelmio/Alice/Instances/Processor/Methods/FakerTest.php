<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\FooProvider;
use PHPUnit\Framework\TestCase;

class FakerTest extends TestCase
{
    public function testAddProvider()
    {
        $faker = new Faker([]);
        $faker->addProvider(new FooProvider());
        $this->assertSame('foo_bar', $faker->fake('foo', null, '_bar'));
    }
}
