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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithConstructor
 */
class ReflectionWithConstructorInstantiatorTest extends TestCase
{
    public function testIsAnInstantiatorMethod()
    {
        $this->assertTrue(
            is_a(
                'Nelmio\Alice\Instances\Instantiator\Methods\ReflectionWithConstructor',
                'Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface',
                true
            )
        );
    }
}
