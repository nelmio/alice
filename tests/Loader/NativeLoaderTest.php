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
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Unknown method "foo".
     */
    public function testThrowsAnExceptionIfCallUnknownMethod()
    {
        $loader = new NativeLoader();
        $loader->foo();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Unknown method "createFoo".
     */
    public function testThrowsAnExceptionIfCallUnknownGetMethod()
    {
        $loader = new NativeLoader();
        $loader->getFoo();
    }

    public function testAlwaysReturnsTheSameService()
    {
        $loader = new NativeLoader();
        $fileLoader1 = $loader->getBuiltInFileLoader();
        $fileLoader2 = $loader->getBuiltInFileLoader();

        $this->assertSame($fileLoader1, $fileLoader2);
    }
}
