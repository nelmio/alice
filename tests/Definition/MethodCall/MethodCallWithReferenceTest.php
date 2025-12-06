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
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(MethodCallWithReference::class)]
final class MethodCallWithReferenceTest extends TestCase
{
    public function testIsAMethodCall(): void
    {
        self::assertTrue(is_a(MethodCallWithReference::class, MethodCallInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];

        $definition = new MethodCallWithReference($caller, $method, $arguments);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());
        self::assertEquals('user.factory->setUsername', $definition->__toString());

        $definition = new MethodCallWithReference($caller, $method, null);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertNull($definition->getArguments());
        self::assertEquals('user.factory->setUsername', $definition->__toString());

        $caller = new StaticReference('Dummy');
        $definition = new MethodCallWithReference($caller, $method, null);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertNull($definition->getArguments());
        self::assertEquals('Dummy::setUsername', $definition->__toString());
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
        // @phpstan-ignore-next-line
        $arguments[0]->foz = 'baz';

        self::assertEquals(new MutableReference(), $definition->getCaller());
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
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];
        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $newArguments = null;
        $newDefinition = $definition->withArguments($newArguments);

        self::assertInstanceOf(MethodCallWithReference::class, $newDefinition);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());

        self::assertEquals($caller, $newDefinition->getCaller());
        self::assertEquals($method, $newDefinition->getMethod());
        self::assertEquals($newArguments, $newDefinition->getArguments());
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

        self::assertInstanceOf(MethodCallWithReference::class, $newDefinition);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());

        self::assertEquals($caller, $newDefinition->getCaller());
        self::assertEquals($method, $newDefinition->getMethod());
        self::assertEquals(
            [
                StdClassFactory::create(['foo' => 'bar']),
            ],
            $newDefinition->getArguments(),
        );
    }
}
