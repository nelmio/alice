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

use Nelmio\Alice\Definition\MethodCallInterface;

/**
 * @covers Nelmio\Alice\Definition\MethodCall\NoMethodCall
 */
class NoMethodCallTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(NoMethodCall::class, MethodCallInterface::class, true));
    }

    public function testAccessors()
    {
        $call = new NoMethodCall();

        $this->assertEquals('none', $call->__toString());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::withArguments()" should not be called.
     */
    public function testCallWithArguments()
    {
        $call = new NoMethodCall();
        $call->withArguments();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getMethod()" should not be called.
     */
    public function testGetMethod()
    {
        $call = new NoMethodCall();
        $call->getMethod();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage By its nature, "Nelmio\Alice\Definition\MethodCall\NoMethodCall::getCaller()" should not be called.
     */
    public function testGetCaller()
    {
        $call = new NoMethodCall();
        $call->getCaller();
    }
}
