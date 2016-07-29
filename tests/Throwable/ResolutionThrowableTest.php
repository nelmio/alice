<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Throwable;

use Nelmio\Alice\Exception\RootResolutionException;

/**
 * @covers Nelmio\Alice\Throwable\ResolutionThrowable
 */
class ResolutionThrowableTest extends \PHPUnit_Framework_TestCase
{
    public function testIsABuildThrowable()
    {
        $this->assertTrue(is_a(RootResolutionException::class, GenerationThrowable::class, true));
    }
}
