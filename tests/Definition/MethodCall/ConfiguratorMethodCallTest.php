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
 */
class ConfiguratorMethodCallTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAMethodCall(): void
    {
        static::assertTrue(is_a(ConfiguratorMethodCall::class, MethodCallInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new stdClass()];
        $percentage = 30;
        $stringValue = 'user.factory->setUsername';

        $methodCallProphecy = $this->prophesize(MethodCallInterface::class);
        $methodCallProphecy->getCaller()->willReturn($caller);
        $methodCallProphecy->getMethod()->willReturn($method);
        $methodCallProphecy->getArguments()->willReturn($arguments);
        $methodCallProphecy->__toString()->willReturn($stringValue);
        /** @var MethodCallInterface $caller */
        $methodCall = $methodCallProphecy->reveal();

        $definition = new ConfiguratorMethodCall($methodCall);

        static::assertEquals($caller, $definition->getCaller());
        static::assertEquals($method, $definition->getMethod());
        static::assertEquals($arguments, $definition->getArguments());
        static::assertEquals($stringValue, $definition->__toString());
        static::assertSame($methodCall, $definition->getOriginalMethodCall());

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
            ]
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

        static::assertEquals('mutated', $definition->getCaller()->getId());
        static::assertEquals('dummy', $definition->getMethod());
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

    public function testCanCreateANewInstanceWithArguments(): void
    {
        $arguments = [new stdClass()];
        $methodCall = new SimpleMethodCall('getUsername', $arguments);
        $definition = new ConfiguratorMethodCall($methodCall);

        $newArguments = [
            new stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        static::assertInstanceOf(ConfiguratorMethodCall::class, $newDefinition);

        static::assertEquals(
            new SimpleMethodCall('getUsername', $newArguments),
            $definition->getOriginalMethodCall()
        );
        static::assertSame($newArguments, $newDefinition->getArguments());
    }
}
