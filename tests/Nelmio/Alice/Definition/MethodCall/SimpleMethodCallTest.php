<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\MethodCallInterface;

/**
 * @covers Nelmio\Alice\Definition\MethodCall\SimpleMethodCall
 */
class SimpleMethodCallTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(SimpleMethodCall::class, MethodCallInterface::class, true));
    }
    
    public function testAccessors()
    {
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new SimpleMethodCall($method, $arguments);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertSame($arguments, $definition->getArguments());
        $this->assertEquals($method, $definition->__toString());
    }
}
