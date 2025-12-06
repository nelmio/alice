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

use BadMethodCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NativeLoader::class)]
final class NativeLoaderTest extends TestCase
{
    public function testThrowsAnExceptionIfCallUnknownMethod(): void
    {
        $loader = new NativeLoader();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method "foo".');

        // @phpstan-ignore-next-line
        $loader->foo();
    }

    public function testThrowsAnExceptionIfCallUnknownGetMethod(): void
    {
        $loader = new NativeLoader();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method "createFoo".');

        // @phpstan-ignore-next-line
        $loader->getFoo();
    }

    public function testAlwaysReturnsTheSameService(): void
    {
        $loader = new NativeLoader();
        $fileLoader1 = $loader->getFileLoader();
        $fileLoader2 = $loader->getFileLoader();

        self::assertSame($fileLoader1, $fileLoader2);
    }
}
