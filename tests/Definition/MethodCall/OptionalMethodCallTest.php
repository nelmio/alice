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

use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\MutableReference;
use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\OptionalMethodCall
 * @internal
 */
class OptionalMethodCallTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAMethodCall(): void
    {
        self::assertTrue(is_a(OptionalMethodCall::class, MethodCallInterface::class, true));
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

        $flag = new OptionalFlag($percentage);

        $definition = new OptionalMethodCall($methodCall, $flag);

        self::assertEquals($caller, $definition->getCaller());
        self::assertEquals($method, $definition->getMethod());
        self::assertEquals($arguments, $definition->getArguments());
        self::assertEquals($percentage, $definition->getPercentage());
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
        $flag = new OptionalFlag(30);

        $definition = new OptionalMethodCall($caller, $flag);

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

    public function testCanCreateANewInstanceWithNoArguments(): void
    {
        $arguments = [new stdClass()];
        $methodCall = new SimpleMethodCall('getUsername', $arguments);
        $definition = new OptionalMethodCall($methodCall, new OptionalFlag(30));

        $newArguments = null;
        $newDefinition = $definition->withArguments($newArguments);

        self::assertInstanceOf(OptionalMethodCall::class, $newDefinition);

        self::assertEquals($methodCall->getCaller(), $definition->getCaller());
        self::assertEquals(30, $definition->getPercentage());
        self::assertEquals($methodCall->getMethod(), $definition->getMethod());
        self::assertEquals($methodCall->getArguments(), $definition->getArguments());

        self::assertEquals($methodCall->getCaller(), $newDefinition->getCaller());
        self::assertEquals(30, $newDefinition->getPercentage());
        self::assertEquals($methodCall->getMethod(), $newDefinition->getMethod());
        self::assertEquals($newArguments, $newDefinition->getArguments());
    }

    public function testCanCreateANewInstanceWithArguments(): void
    {
        $methodCall = new SimpleMethodCall('getUsername', null);
        $definition = new OptionalMethodCall($methodCall, new OptionalFlag(30));

        $newArguments = [
            $arg0 = new stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        // Mutate arguments before reading it
        $arg0->foo = 'bar';

        self::assertInstanceOf(OptionalMethodCall::class, $newDefinition);

        self::assertEquals($methodCall->getCaller(), $definition->getCaller());
        self::assertEquals(30, $definition->getPercentage());
        self::assertEquals($methodCall->getMethod(), $definition->getMethod());
        self::assertEquals($methodCall->getArguments(), $definition->getArguments());

        self::assertEquals($methodCall->getCaller(), $newDefinition->getCaller());
        self::assertEquals(30, $newDefinition->getPercentage());
        self::assertEquals($methodCall->getMethod(), $newDefinition->getMethod());
        self::assertEquals(
            [
                StdClassFactory::create(['foo' => 'bar']),
            ],
            $newDefinition->getArguments(),
        );
    }
}
