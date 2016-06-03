<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture\Chainable;

use Nelmio\Alice\Builder\Fixture\ChainableFixtureBuilderInterface;

/**
 * @covers Nelmio\Alice\Builder\Fixture\Chainable\ListNameBuilder
 */
class ListNameBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableUnresolvedFixtureBuilder()
    {
        $this->assertTrue(is_a(ListNameBuilder::class, ChainableFixtureBuilderInterface::class, true));
    }
}
