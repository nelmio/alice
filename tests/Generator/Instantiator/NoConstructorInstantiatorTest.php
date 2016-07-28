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

use Nelmio\Alice\Generator\Instantiator\Chainable\NoConstructorInstantiator;

/**
 * @covers Nelmio\Alice\Generator\Instantiator\NoConstructorInstantiator
 */
class NoConstructorInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableInstantiator()
    {
        $this->assertTrue(is_a(NoConstructorInstantiator::class, ChainableInstantiatorInterface::class, true));
    }
}
