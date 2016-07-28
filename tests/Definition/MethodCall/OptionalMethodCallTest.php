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

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;

/**
 * @covers Nelmio\Alice\Definition\MethodCall\OptionalMethodCall
 */
class OptionalMethodCallTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(OptionalMethodCall::class, MethodCallInterface::class, true));
    }
    
    public function testAccessors()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];
        $percentage = 30;
        $stringValue = 'user.factorysetUsername';

        $methodCallProphecy = $this->prophesize(MethodCallInterface::class);
        $methodCallProphecy->getCaller()->willReturn($caller);
        $methodCallProphecy->getMethod()->willReturn($method);
        $methodCallProphecy->getArguments()->willReturn($arguments);
        $methodCallProphecy->__toString()->willReturn($stringValue);
        /** @var MethodCallInterface $methodCall */
        $methodCall = $methodCallProphecy->reveal();

        $flag = new OptionalFlag($percentage);

        $definition = new OptionalMethodCall($methodCall, $flag);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertSame($arguments, $definition->getArguments());
        $this->assertEquals($percentage, $definition->getPercentage());
        $this->assertEquals($stringValue, $definition->__toString());

        $methodCallProphecy->getCaller()->shouldHaveBeenCalledTimes(1);
        $methodCallProphecy->getMethod()->shouldHaveBeenCalledTimes(1);
        $methodCallProphecy->getArguments()->shouldHaveBeenCalledTimes(1);
    }

    public function testImmutableMutator()
    {
        $methodCall = new SimpleMethodCall('getUsername', null);
        $definition = new OptionalMethodCall($methodCall, new OptionalFlag(30));

        $newArguments = [new \stdClass()];
        $newDefinition = $definition->withArguments($newArguments);

        $this->assertInstanceOf(OptionalMethodCall::class, $newDefinition);

        $this->assertEquals($methodCall->getCaller(), $definition->getCaller());
        $this->assertEquals(30, $definition->getPercentage());
        $this->assertEquals($methodCall->getMethod(), $definition->getMethod());
        $this->assertEquals($methodCall->getArguments(), $definition->getArguments());

        $this->assertEquals($methodCall->getCaller(), $newDefinition->getCaller());
        $this->assertEquals(30, $newDefinition->getPercentage());
        $this->assertEquals($methodCall->getMethod(), $newDefinition->getMethod());
        $this->assertEquals($newArguments, $newDefinition->getArguments());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $methodCall = new FakeMethodCall();
        $flag = new OptionalFlag(30);

        $definition = new OptionalMethodCall($methodCall, $flag);
        clone $definition;
    }
}
