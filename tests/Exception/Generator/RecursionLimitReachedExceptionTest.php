<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator;

use Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException
 */
class RecursionLimitReachedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAResolutionThrowable()
    {
        $this->assertTrue(is_a(UniqueValueGenerationLimitReachedException::class, ResolutionThrowable::class, true));
    }
}
