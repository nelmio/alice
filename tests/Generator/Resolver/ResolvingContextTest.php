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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\ResolvingContext
 */
class ResolvingContextTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $context = new ResolvingContext();
        $this->assertFalse($context->has('foo'));

        $context = new ResolvingContext('foo');
        $this->assertTrue($context->has('foo'));
    }

    public function testMutator()
    {
        $context = new ResolvingContext();

        $this->assertFalse($context->has('foo'));
        $context->add('foo');
        $this->assertTrue($context->has('foo'));
    }

    public function testStaticFactoryMethodReturnsExistingInstance()
    {
        $context = ResolvingContext::createFrom(null, 'foo');
        $this->assertTrue($context->has('foo'));

        $newContext = ResolvingContext::createFrom($context, 'ping');
        $this->assertSame($context, $newContext);
        $this->assertTrue($context->has('foo'));
        $this->assertTrue($context->has('ping'));
    }

    public function testFactoryMethodCannotTriggerACircularReference()
    {
        $context = new ResolvingContext('foo');
        $context->checkForCircularReference('foo');

        $context = ResolvingContext::createFrom($context, 'foo');
        $context->checkForCircularReference('foo');

        $context = ResolvingContext::createFrom($context, 'foo');
        $context->checkForCircularReference('foo');

        $context->add('foo');
        try {
            $context->checkForCircularReference('foo');
            $this->fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            // Expected result
        }
    }

    public function testThrowsAnExceptionWhenACircularReferenceIsDetected()
    {
        $context = new ResolvingContext('bar');
        $context->checkForCircularReference('foo');

        $context->add('foo');
        $context->checkForCircularReference('foo');

        $context->add('foo');
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

        $context->add('foo');
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
