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
 * @internal
 */
class ResolvingContextTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $context = new ResolvingContext();
        self::assertFalse($context->has('foo'));

        $context = new ResolvingContext('foo');
        self::assertTrue($context->has('foo'));
    }

    public function testMutator(): void
    {
        $context = new ResolvingContext();

        self::assertFalse($context->has('foo'));
        $context->add('foo');
        self::assertTrue($context->has('foo'));
    }

    public function testStaticFactoryMethodReturnsExistingInstance(): void
    {
        $context = ResolvingContext::createFrom(null, 'foo');
        self::assertTrue($context->has('foo'));

        $newContext = ResolvingContext::createFrom($context, 'ping');
        self::assertSame($context, $newContext);
        self::assertTrue($context->has('foo'));
        self::assertTrue($context->has('ping'));
    }

    public function testFactoryMethodCannotTriggerACircularReference(): void
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
            self::fail('Expected exception to be thrown.');
        } catch (CircularReferenceException) {
            // Expected result
        }
    }

    public function testThrowsAnExceptionWhenACircularReferenceIsDetected(): void
    {
        $context = new ResolvingContext('bar');
        $context->checkForCircularReference('foo');

        $context->add('foo');
        $context->checkForCircularReference('foo');

        $context->add('foo');

        try {
            $context->checkForCircularReference('foo');
            self::fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            self::assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].',
                $exception->getMessage(),
            );
        }

        $context = new ResolvingContext('foo');
        $context->checkForCircularReference('foo');

        $context->add('foo');

        try {
            $context->checkForCircularReference('foo');
            self::fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            self::assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["foo"].',
                $exception->getMessage(),
            );
        }
    }
}
