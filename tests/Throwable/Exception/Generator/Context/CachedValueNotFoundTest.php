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

namespace Nelmio\Alice\Throwable\Exception\Generator\Context;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Context\CachedValueNotFound
 */
class CachedValueNotFoundTest extends TestCase
{
    public function testCreate(): void
    {
        $exception = CachedValueNotFound::create('foo');

        static::assertEquals(
            'No value with the key "foo" was found in the cache.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
