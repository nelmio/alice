<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

/**
 * @covers Nelmio\Alice\Resolver\Parameter\ResolvingContext
 */
class ResolvingContextTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $context = new ResolvingContext();
        $this->assertFalse($context->has('foo'));

        $context = new ResolvingContext('foo');
        $this->assertTrue($context->has('foo'));
    }

    public function testImmutableMutators()
    {
        $context = new ResolvingContext();
        $newContext = $context->with('foo');

        $this->assertInstanceOf(ResolvingContext::class, $newContext);
        $this->assertNotSame($newContext, $context);
        $this->assertFalse($context->has('foo'));
        $this->assertTrue($newContext->has('foo'));
    }

    public function testFactoryMethod()
    {
        $context = ResolvingContext::createFrom();
        $this->assertFalse($context->has('foo'));

        $newContext = ResolvingContext::createFrom($context->with('foo'));
        $this->assertFalse($context->has('foo'));
        $this->assertTrue($newContext->has('foo'));

        $this->assertNotSame($newContext, $context);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Resolver\CircularReferenceException
     * @expectedExceptionMessage Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].
     */
    public function testCheckForCircularReferences()
    {
        $context = new ResolvingContext('bar');
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
        $context = new ResolvingContext('foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = $context->with('foo');
        $context->checkForCircularReference('foo');
        $this->assertFalse(false, 'Expected exception to be thrown.');
    }
}
