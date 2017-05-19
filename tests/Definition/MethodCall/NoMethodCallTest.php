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
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\NoMethodCall
 */
class NoMethodCallTest extends TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(NoMethodCall::class, MethodCallInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $call = new NoMethodCall();

        $this->assertEquals('none', $call->__toString());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::withArguments()" should not be called.
     */
    public function testCannotCreateNewInstanceWithNewArguments()
    {
        $call = new NoMethodCall();
        $call->withArguments();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getCaller()" should not be called.
     */
    public function testCannotGetCaller()
    {
        $call = new NoMethodCall();
        $call->getCaller();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getMethod()" should not be called.
     */
    public function testCannotGetMethod()
    {
        $call = new NoMethodCall();
        $call->getMethod();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getArguments()" should not be called.
     */
    public function testCannotGetArguments()
    {
        $call = new NoMethodCall();
        $call->getArguments();
    }
}
