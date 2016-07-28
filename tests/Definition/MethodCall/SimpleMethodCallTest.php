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

        $definition = new SimpleMethodCall($method, null);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertNull($definition->getArguments());
        $this->assertEquals($method, $definition->__toString());
    }

    public function testImmutableMutator()
    {
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new SimpleMethodCall($method, $arguments);
        $newDefinition = $definition->withArguments(null);

        $this->assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertSame($arguments, $definition->getArguments());
        $this->assertEquals($method, $definition->__toString());

        $this->assertNull($newDefinition->getCaller());
        $this->assertEquals($method, $newDefinition->getMethod());
        $this->assertNull($newDefinition->getArguments());
        $this->assertEquals($method, $newDefinition->__toString());
    }
}
