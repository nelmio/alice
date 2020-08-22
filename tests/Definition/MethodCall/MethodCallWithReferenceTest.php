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
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\MutableReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\MethodCallWithReference
 */
class MethodCallWithReferenceTest extends TestCase
{
    public function testIsAMethodCall(): void
    {
        static::assertTrue(is_a(MethodCallWithReference::class, MethodCallInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];

        $definition = new MethodCallWithReference($caller, $method, $arguments);

        static::assertEquals($caller, $definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());
        static::assertEquals('user.factory->setUsername', $definition->__toString());

        $definition = new MethodCallWithReference($caller, $method, null);

        static::assertEquals($caller, $definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertNull($definition->getArguments());
        static::assertEquals('user.factory->setUsername', $definition->__toString());

        $caller = new StaticReference('Dummy');
        $definition = new MethodCallWithReference($caller, $method, null);

        static::assertEquals($caller, $definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertNull($definition->getArguments());
        static::assertEquals('Dummy::setUsername', $definition->__toString());
    }

    public function testIsMutable(): void
    {
        $caller = new MutableReference();
        $method = 'setUsername';
        $arguments = [
            $arg0 = new stdClass(),
        ];

        $definition = new MethodCallWithReference($caller, $method, $arguments);

        // Mutate injected elements
        $caller->setId('user.factory');
        $arg0->foo = 'bar';

        // Mutate retrieved elements
        /** @var MutableReference $caller */
        $caller = $definition->getCaller();
        $caller->setId('user.factory');
        $arguments = $definition->getArguments();
        $arguments[0]->foz = 'baz';

        static::assertEquals(new MutableReference(), $definition->getCaller());
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
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];
        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $newArguments = null;
        $newDefinition = $definition->withArguments($newArguments);

        static::assertInstanceOf(MethodCallWithReference::class, $newDefinition);

        static::assertEquals($caller, $definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());

        static::assertEquals($caller, $newDefinition->getCaller());
        static::assertEquals($method, $newDefinition->getMethod());
        static::assertEquals($newArguments, $newDefinition->getArguments());
    }

    public function testCanCreateANewInstanceWithArguments(): void
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];
        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $newArguments = [
            $arg0 = new stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        // Mutate argument before reading it
        $arg0->foo = 'bar';

        static::assertInstanceOf(MethodCallWithReference::class, $newDefinition);

        static::assertEquals($caller, $definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());

        static::assertEquals($caller, $newDefinition->getCaller());
        static::assertEquals($method, $newDefinition->getMethod());
        static::assertEquals(
            [
                StdClassFactory::create(['foo' => 'bar']),
            ],
            $newDefinition->getArguments()
        );
    }
}
