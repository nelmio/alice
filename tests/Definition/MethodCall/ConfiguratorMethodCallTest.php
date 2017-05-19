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

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\ConfiguratorMethodCall
 */
class ConfiguratorMethodCallTest extends TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(ConfiguratorMethodCall::class, MethodCallInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];
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

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());
        $this->assertEquals($stringValue, $definition->__toString());
        $this->assertSame($methodCall, $definition->getOriginalMethodCall());

        $methodCallProphecy->getCaller()->shouldHaveBeenCalledTimes(1);
        $methodCallProphecy->getMethod()->shouldHaveBeenCalledTimes(1);
        $methodCallProphecy->getArguments()->shouldHaveBeenCalledTimes(1);
    }

    public function testIsMutable()
    {
        $caller = new MutableMethodCall(
            new MutableReference(),
            'mutate',
            [
                $arg0 = new \stdClass(),
            ]
        );

        $definition = new ConfiguratorMethodCall($caller);

        // Mutate before reading values
        $caller->setMethod('dummy');
        $arg0->foo = 'bar';

        // Mutate retrieved values
        $definition->getCaller()->setId('mutated');
        $definition->getArguments()[0]->foz = 'baz';

        $this->assertEquals('mutated', $definition->getCaller()->getId());
        $this->assertEquals('dummy', $definition->getMethod());
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

    public function testCanCreateANewInstanceWithArguments()
    {
        $arguments = [new \stdClass()];
        $methodCall = new SimpleMethodCall('getUsername', $arguments);
        $definition = new ConfiguratorMethodCall($methodCall);

        $newArguments = [
            new \stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        $this->assertInstanceOf(ConfiguratorMethodCall::class, $newDefinition);

        $this->assertEquals(
            new SimpleMethodCall('getUsername', $newArguments),
            $definition->getOriginalMethodCall()
        );
        $this->assertSame($newArguments, $newDefinition->getArguments());
    }
}
