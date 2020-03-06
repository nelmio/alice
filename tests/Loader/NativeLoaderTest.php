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

namespace Nelmio\Alice\Loader;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Loader\NativeLoader
 */
class NativeLoaderTest extends TestCase
{
    public function testThrowsAnExceptionIfCallUnknownMethod()
    {
        $loader = new NativeLoader();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method "foo".');

        $loader->foo();
    }

    public function testThrowsAnExceptionIfCallUnknownGetMethod()
    {
        $loader = new NativeLoader();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method "createFoo".');

        $loader->getFoo();
    }

    public function testAlwaysReturnsTheSameService()
    {
        $loader = new NativeLoader();
        $fileLoader1 = $loader->getFileLoader();
        $fileLoader2 = $loader->getFileLoader();

        $this->assertSame($fileLoader1, $fileLoader2);
    }
}
