<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use PhpUnit\PhpUnit;

/**
 * @covers Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithConstructor
 */
class ReflectionWithConstructorInstantiatorTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_an_instantiator_method()
    {
        PhpUnit::assertIsA(
            'Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface',
            'Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithConstructor'
        );
    }
}
