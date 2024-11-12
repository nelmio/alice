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
use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\ConfiguratorMethodCall
 * @internal
 */
class ConfiguratorMethodCallTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAMethodCall(): void
    {
        self::assertTrue(is_a(ConfiguratorMethodCall::class, MethodCallInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];
        $stringValue = 'user.factory->setUsername';

        $methodCallProphecy = $this->prophesize(MethodCallInterface::class);
        $methodCallProphecy->getCaller()->willReturn($caller);
        $methodCallProphecy->getMethod()->willReturn($method);
        $methodCallProphecy->getArguments()->willReturn($arguments);
        $methodCallProphecy->__toString()->willReturn($stringValue);
        $methodCall = $methodCallProphecy->reveal();

        $definition = new ConfiguratorMethodCall($methodCall);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());
        self::assertEquals($stringValue, $definition->__toString());
        self::assertSame($methodCall, $definition->getOriginalMethodCall());

        $methodCallProphecy->getCaller()->shouldHaveBeenCalledTimes(1);
        $methodCallProphecy->getMethod()->shouldHaveBeenCalledTimes(1);
        $methodCallProphecy->getArguments()->shouldHaveBeenCalledTimes(1);
    }

    public function testIsMutable(): void
    {
        $caller = new MutableMethodCall(
            new MutableReference(),
            'mutate',
            [
                $arg0 = new stdClass(),
            ],
        );

        $definition = new ConfiguratorMethodCall($caller);

        // Mutate before reading values
        $caller->setMethod('dummy');
        $arg0->foo = 'bar';

        // Mutate retrieved values
        // @phpstan-ignore-next-line
        $definition->getCaller()->setId('mutated');
        // @phpstan-ignore-next-line
        $definition->getArguments()[0]->foz = 'baz';

        self::assertEquals('mutated', $definition->getCaller()->getId());
        self::assertEquals('dummy', $definition->getMethod());
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

    public function testCanCreateANewInstanceWithArguments(): void
    {
        $arguments = [new stdClass()];
        $methodCall = new SimpleMethodCall('getUsername', $arguments);
        $definition = new ConfiguratorMethodCall($methodCall);

        $newArguments = [
            new stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        self::assertInstanceOf(ConfiguratorMethodCall::class, $newDefinition);

        self::assertEquals(
            new SimpleMethodCall('getUsername', $newArguments),
            $definition->getOriginalMethodCall(),
        );
        self::assertSame($newArguments, $newDefinition->getArguments());
    }
}
