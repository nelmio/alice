<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

/**
 * @covers Nelmio\Alice\Loader\NativeLoader
 */
class NativeLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAlwaysReturnsTheSameService()
    {
        $loader = new NativeLoader();
        $pool1 = $loader->getBuiltInUniqueValuesPool();
        $pool2 = $loader->getBuiltInUniqueValuesPool();

        $this->assertSame($pool1, $pool2);
    }
}
