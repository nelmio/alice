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

namespace Nelmio\Alice\Throwable;

use Nelmio\Alice\Throwable\Exception\RootDenormalizationException;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class DenormalizationThrowableTest extends TestCase
{
    public function testIsABuildThrowable()
    {
        $this->assertTrue(is_a(RootDenormalizationException::class, BuildThrowable::class, true));
    }
}
