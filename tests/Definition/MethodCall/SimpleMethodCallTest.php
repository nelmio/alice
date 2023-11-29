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
 * @internal
 */
class SimpleMethodCallTest extends TestCase
{
    public function testIsAMethodCall(): void
    {
        self::assertTrue(is_a(SimpleMethodCall::class, MethodCallInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $method = 'setUsername';
        $arguments = [new stdClass()];

        $definition = new SimpleMethodCall($method, $arguments);

        self::assertNull($definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());
        self::assertEquals($method, $definition->__toString());

        $definition = new SimpleMethodCall($method, null);

        self::assertNull($definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertNull($definition->getArguments());
        self::assertEquals($method, $definition->__toString());
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
        // @phpstan-ignore-next-line
        $definition->getArguments()[0]->foz = 'baz';

        self::assertEquals(
            [
                StdClassFactory::create([
                    'foo' => 'bar',
                    'foz' => 'baz',
                ]),
            ],
            $definition->getArguments(),
        );
    }

    public function testCanCreateANewInstanceWithNoArguments(): void
    {
        $method = 'setUsername';
        $arguments = null;
        $definition = new SimpleMethodCall($method, $arguments);

        $newArguments = [new stdClass()];
        $newDefinition = $definition->withArguments($newArguments);

        self::assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        self::assertNull($definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());
        self::assertEquals($method, $definition->__toString());

        self::assertNull($newDefinition->getCaller());
        self::assertEquals($method, $newDefinition->getMethod());
        self::assertEquals($newArguments, $newDefinition->getArguments());
        self::assertEquals($method, $newDefinition->__toString());
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

        self::assertInstanceOf(SimpleMethodCall::class, $newDefinition);

        self::assertNull($definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());
        self::assertEquals($method, $definition->__toString());

        self::assertNull($newDefinition->getCaller());
        self::assertEquals($method, $newDefinition->getMethod());
        self::assertEquals(
            [
                StdClassFactory::create([
                    'foo' => 'bar',
                ]),
            ],
            $newDefinition->getArguments(),
        );
        self::assertEquals($method, $newDefinition->__toString());
    }
}
