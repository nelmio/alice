<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder;

/**
 * @covers Nelmio\Alice\FixtureBuilder\BareFixtureSet
 */
class BareFixtureSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @depends Nelmio\Alice\ParameterBagTest::testIsImmutable
     * @depends Nelmio\Alice\FixtureBagTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }
}
