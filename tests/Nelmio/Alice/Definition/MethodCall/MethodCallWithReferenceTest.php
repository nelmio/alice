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
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;

/**
 * @covers Nelmio\Alice\Definition\MethodCall\MethodCallWithReference
 */
class MethodCallWithReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(MethodCallWithReference::class, MethodCallInterface::class, true));
    }
    
    public function testAccessors()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertSame($arguments, $definition->getArguments());
        $this->assertEquals('user.factorysetUsername', $definition->__toString());
    }

    public function testIsImmutable()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $this->assertNotSame($definition->getCaller(), $definition->getCaller());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new MethodCallWithReference($caller, $method, $arguments);
        clone $definition;
    }
}
