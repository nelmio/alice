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
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\SimpleMethodCall
 */
class SimpleMethodCallTest extends TestCase
{
    public function testIsAMethodCall(): void
    {
        static::assertTrue(is_a(SimpleMethodCall::class, MethodCallInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $method = 'setUsername';
        $arguments = [new stdClass()];

        $definition = new SimpleMethodCall($method, $arguments);

        static::assertNull($definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());
        static::assertEquals($method, $definition->__toString());

        $definition = new SimpleMethodCall($method, null);

        static::assertNull($definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertNull($definition->getArguments());
        static::assertEquals($method, $definition->__toString());
    }

    public function testIsMutable(): void
    {
        $arguments = [
            $arg0 = new stdClass(),
        ];
        $definition = new SimpleMethodCall('foo', $arguments);

        // Mutate before reading values
        $arg0->foo = 'bar';

        // Mutate retrieved values
        $definition->getArguments()[0]->foz = 'baz';

        static::assertEquals(
            [
                StdClassFactory::create([
                    'foo' => 'bar',
                    'foz' => 'baz',
                ]),
            ],
            $definition->getArguments()
        );
    }

    public function testCanCreateANewInstanceWithNoArguments(): void
    {
        $method = 'setUsername';
        $arguments = null;
        $definition = new SimpleMethodCall($method, $arguments);

        $newArguments = [new stdClass()];
        $newDefinition = $definition->withArguments($newArguments);

        static::assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        static::assertNull($definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());
        static::assertEquals($method, $definition->__toString());

        static::assertNull($newDefinition->getCaller());
        static::assertEquals($method, $newDefinition->getMethod());
        static::assertEquals($newArguments, $newDefinition->getArguments());
        static::assertEquals($method, $newDefinition->__toString());
    }

    public function testCanCreateANewInstanceWithArguments(): void
    {
        $method = 'setUsername';
        $arguments = null;
        $definition = new SimpleMethodCall($method, $arguments);

        $newArguments = [
            $arg0 = new stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        // Mutate before reading values
        $arg0->foo = 'bar';

        static::assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        static::assertNull($definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());
        static::assertEquals($method, $definition->__toString());

        static::assertNull($newDefinition->getCaller());
        static::assertEquals($method, $newDefinition->getMethod());
        static::assertEquals(
            [
                StdClassFactory::create([
                    'foo' => 'bar',
                ]),
            ],
            $newDefinition->getArguments()
        );
        static::assertEquals($method, $newDefinition->__toString());
    }
}
