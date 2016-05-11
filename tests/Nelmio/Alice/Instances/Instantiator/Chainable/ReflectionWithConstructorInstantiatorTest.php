<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Chainable;

use Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface;
use PhpUnit\PhpUnit;

/**
 * @covers Nelmio\Alice\Instances\Instantiator\Chainable\ReflectionWithConstructorInstantiator
 */
class ReflectionWithConstructorInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_a_chainable_instantiator()
    {
        PhpUnit::assertIsA(ChainableInstantiatorInterface::class, ReflectionWithConstructorInstantiator::class);
    }
}








