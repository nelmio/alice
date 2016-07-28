<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;

/**
 * @covers Nelmio\Alice\Generator\Instantiator\NoCallerMethodCallInstantiator
 */
class NoCallerMethodCallInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableInstantiator()
    {
        $this->assertTrue(is_a(NoCallerMethodCallInstantiator::class, ChainableInstantiatorInterface::class, true));
    }
}
