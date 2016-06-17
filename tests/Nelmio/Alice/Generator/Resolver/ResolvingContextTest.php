<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver;

/**
 * @covers Nelmio\Alice\Generator\Resolver\ResolvingContext
 */
class ResolvingContextTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext();
        $this->assertFalse($context->has('foo'));

        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext('foo');
        $this->assertTrue($context->has('foo'));
    }

    public function testImmutableMutators()
    {
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext();
        $newContext = $context->with('foo');

        $this->assertInstanceOf(\Nelmio\Alice\Generator\Resolver\ParameterResolvingContext::class, $newContext);
        $this->assertNotSame($newContext, $context);
        $this->assertFalse($context->has('foo'));
        $this->assertTrue($newContext->has('foo'));
    }

    public function testFactoryMethod()
    {
        $context = ParameterResolvingContext::createFrom(null, 'foo');
        $this->assertTrue($context->has('foo'));

        $newContext = ParameterResolvingContext::createFrom($context->with('bar'), 'ping');
        $this->assertFalse($context->has('bar'));
        $this->assertFalse($context->has('ping'));
        $this->assertTrue($newContext->has('foo'));
        $this->assertTrue($newContext->has('bar'));
        $this->assertTrue($newContext->has('ping'));
        $this->assertNotSame($newContext, $context);

        $newContext = \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext::createFrom($context, 'bar');
        $this->assertFalse($context->has('bar'));
        $this->assertTrue($newContext->has('foo'));
        $this->assertTrue($newContext->has('bar'));
        $this->assertNotSame($newContext, $context);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Resolver\CircularReferenceException
     */
    public function testFactoryMethodCannotTriggerCircularReference()
    {
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext('foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = ParameterResolvingContext::createFrom($context, 'foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext::createFrom($context, 'foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = $context->with('foo');
        $context->checkForCircularReference('foo');
        $this->fail('Expected exception to be thrown.');
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Resolver\CircularReferenceException
     * @expectedExceptionMessage Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].
     */
    public function testCheckForCircularReferences()
    {
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext('bar');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = $context->with('foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = $context->with('foo');
        $context->checkForCircularReference('foo');
        $this->assertFalse(false, 'Expected exception to be thrown.');
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Resolver\CircularReferenceException
     * @expectedExceptionMessage Circular reference detected for the parameter "foo" while resolving ["foo"].
     */
    public function testCheckForCircularReferencesWithInitializedConstructor()
    {
        $context = new \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext('foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = $context->with('foo');
        $context->checkForCircularReference('foo');
        $this->assertFalse(false, 'Expected exception to be thrown.');
    }
}
