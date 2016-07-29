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

use Nelmio\Alice\Exception\RootInstantiationException;

/**
 * @covers Nelmio\Alice\Throwable\InstantiationThrowable
 */
class InstantiationThrowableTest extends \PHPUnit_Framework_TestCase
{
    public function testIsABuildThrowable()
    {
        $this->assertTrue(is_a(RootInstantiationException::class, GenerationThrowable::class, true));
    }
}
