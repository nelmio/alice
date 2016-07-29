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

use Nelmio\Alice\Exception\Generator\Resolver\CircularReferenceException;

/**
 * @covers Nelmio\Alice\Generator\Resolver\ResolvingContext
 */
class ResolvingContextTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $context = new ResolvingContext();
        $this->assertFalse($context->has('foo'));

        $context = new ResolvingContext('foo');
        $this->assertTrue($context->has('foo'));
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $context = new ResolvingContext();
        $newContext = $context->with('foo');

        $this->assertInstanceOf(ResolvingContext::class, $newContext);
        $this->assertNotSame($newContext, $context);
        $this->assertFalse($context->has('foo'));
        $this->assertTrue($newContext->has('foo'));
    }

    public function testStaticFactoryMethodCreatesANewInstance()
    {
        $context = ResolvingContext::createFrom(null, 'foo');
        $this->assertTrue($context->has('foo'));

        $newContext = ResolvingContext::createFrom($context->with('bar'), 'ping');
        $this->assertFalse($context->has('bar'));
        $this->assertFalse($context->has('ping'));
        $this->assertTrue($newContext->has('foo'));
        $this->assertTrue($newContext->has('bar'));
        $this->assertTrue($newContext->has('ping'));
        $this->assertNotSame($newContext, $context);

        $newContext = ResolvingContext::createFrom($context, 'bar');
        $this->assertFalse($context->has('bar'));
        $this->assertTrue($newContext->has('foo'));
        $this->assertTrue($newContext->has('bar'));
        $this->assertNotSame($newContext, $context);
    }

    public function testFactoryMethodCannotTriggerACircularReference()
    {
        $context = new ResolvingContext('foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = ResolvingContext::createFrom($context, 'foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = ResolvingContext::createFrom($context, 'foo');
        $context->checkForCircularReference('foo');
        $this->assertTrue(true, 'Did not expect exception to be thrown.');

        $context = $context->with('foo');
        try {
            $context->checkForCircularReference('foo');
            $this->fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            // expected result
        }
    }

    public function testThrowsAnExceptionWhenACircularReferenceIsDetected()
    {
        $context = new ResolvingContext('bar');
        $context->checkForCircularReference('foo');

        $context = $context->with('foo');
        $context->checkForCircularReference('foo');

        $context = $context->with('foo');
        try {
            $context->checkForCircularReference('foo');
            $this->fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            $this->assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].',
                $exception->getMessage()
            );
        }

        $context = new ResolvingContext('foo');
        $context->checkForCircularReference('foo');

        $context = $context->with('foo');
        try {
            $context->checkForCircularReference('foo');
            $this->fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            $this->assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["foo"].',
                $exception->getMessage()
            );
        }
    }
}
