<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\SimpleMethodCall
 */
class SimpleMethodCallTest extends TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(SimpleMethodCall::class, MethodCallInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new SimpleMethodCall($method, $arguments);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());
        $this->assertEquals($method, $definition->__toString());

        $definition = new SimpleMethodCall($method, null);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertNull($definition->getArguments());
        $this->assertEquals($method, $definition->__toString());
    }

    public function testIsMutable()
    {
        $arguments = [
            $arg0 = new \stdClass(),
        ];
        $definition = new SimpleMethodCall('foo', $arguments);

        // Mutate before reading values
        $arg0->foo = 'bar';

        // Mutate retrieved values
        $definition->getArguments()[0]->foz = 'baz';

        $this->assertEquals(
            [
                StdClassFactory::create([
                    'foo' => 'bar',
                    'foz' => 'baz',
                ]),
            ],
            $definition->getArguments()
        );
    }

    public function testCanCreateANewInstanceWithNoArguments()
    {
        $method = 'setUsername';
        $arguments = null;
        $definition = new SimpleMethodCall($method, $arguments);

        $newArguments = [new \stdClass()];
        $newDefinition = $definition->withArguments($newArguments);

        $this->assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());
        $this->assertEquals($method, $definition->__toString());

        $this->assertNull($newDefinition->getCaller());
        $this->assertEquals($method, $newDefinition->getMethod());
        $this->assertEquals($newArguments, $newDefinition->getArguments());
        $this->assertEquals($method, $newDefinition->__toString());
    }

    public function testCanCreateANewInstanceWithArguments()
    {
        $method = 'setUsername';
        $arguments = null;
        $definition = new SimpleMethodCall($method, $arguments);

        $newArguments = [
            $arg0 = new \stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        // Mutate before reading values
        $arg0->foo = 'bar';

        $this->assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        $this->assertNull($definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());
        $this->assertEquals($method, $definition->__toString());

        $this->assertNull($newDefinition->getCaller());
        $this->assertEquals($method, $newDefinition->getMethod());
        $this->assertEquals(
            [
                StdClassFactory::create([
                    'foo' => 'bar',
                ]),
            ],
            $newDefinition->getArguments()
        );
        $this->assertEquals($method, $newDefinition->__toString());
    }
}
