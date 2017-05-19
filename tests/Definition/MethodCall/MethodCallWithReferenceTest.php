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

/**
 * @covers \Nelmio\Alice\Definition\MethodCall\MethodCallWithReference
 */
class MethodCallWithReferenceTest extends TestCase
{
    public function testIsAMethodCall()
    {
        $this->assertTrue(is_a(MethodCallWithReference::class, MethodCallInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];

        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());
        $this->assertEquals('user.factory->setUsername', $definition->__toString());

        $definition = new MethodCallWithReference($caller, $method, null);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertNull($definition->getArguments());
        $this->assertEquals('user.factory->setUsername', $definition->__toString());

        $caller = new StaticReference('Dummy');
        $definition = new MethodCallWithReference($caller, $method, null);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertNull($definition->getArguments());
        $this->assertEquals('Dummy::setUsername', $definition->__toString());
    }

    public function testIsMutable()
    {
        $caller = new MutableReference();
        $method = 'setUsername';
        $arguments = [
            $arg0 = new \stdClass(),
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

        $this->assertEquals(new MutableReference(), $definition->getCaller());
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

    public function testCanCreateANewInstanceWithNoArguments()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];
        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $newArguments = null;
        $newDefinition = $definition->withArguments($newArguments);

        $this->assertInstanceOf(MethodCallWithReference::class, $newDefinition);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());

        $this->assertEquals($caller, $newDefinition->getCaller());
        $this->assertEquals($method, $newDefinition->getMethod());
        $this->assertEquals($newArguments, $newDefinition->getArguments());
    }

    public function testCanCreateANewInstanceWithArguments()
    {
        $caller = new InstantiatedReference('user.factory');
        $method = 'setUsername';
        $arguments = [new \stdClass()];
        $definition = new MethodCallWithReference($caller, $method, $arguments);

        $newArguments = [
            $arg0 = new \stdClass(),
        ];
        $newDefinition = $definition->withArguments($newArguments);

        // Mutate argument before reading it
        $arg0->foo = 'bar';

        $this->assertInstanceOf(MethodCallWithReference::class, $newDefinition);

        $this->assertEquals($caller, $definition->getCaller());
        $this->assertEquals($method, $definition->getMethod());
        $this->assertEquals($arguments, $definition->getArguments());

        $this->assertEquals($caller, $newDefinition->getCaller());
        $this->assertEquals($method, $newDefinition->getMethod());
        $this->assertEquals(
            [
                StdClassFactory::create(['foo' => 'bar']),
            ],
            $newDefinition->getArguments()
        );
    }
}
