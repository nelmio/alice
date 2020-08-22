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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Throwable\Exception\Generator\Context\CachedValueNotFound;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\GenerationContext
 */
class GenerationContextTest extends TestCase
{
    public function testAccessors(): void
    {
        $context = new GenerationContext();
        static::assertTrue($context->isFirstPass());
        static::assertFalse($context->needsCompleteGeneration());

        $context->setToSecondPass();
        static::assertFalse($context->isFirstPass());
        static::assertFalse($context->needsCompleteGeneration());
        static::assertFalse($context->needsCallResult());

        $context->markAsNeedsCompleteGeneration();
        static::assertFalse($context->isFirstPass());
        static::assertTrue($context->needsCompleteGeneration());
        static::assertFalse($context->needsCallResult());

        $context->unmarkAsNeedsCompleteGeneration();
        static::assertFalse($context->isFirstPass());
        static::assertFalse($context->needsCompleteGeneration());
        static::assertFalse($context->needsCallResult());

        $context->markRetrieveCallResult();
        static::assertFalse($context->isFirstPass());
        static::assertFalse($context->needsCompleteGeneration());
        static::assertTrue($context->needsCallResult());

        $context->unmarkRetrieveCallResult();
        static::assertFalse($context->isFirstPass());
        static::assertFalse($context->needsCompleteGeneration());
        static::assertFalse($context->needsCallResult());
    }

    public function testThrowsAnExceptionWhenACircularReferenceIsDetected(): void
    {
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');
        $context->markIsResolvingFixture('foo');

        try {
            $context->markIsResolvingFixture('foo');
            static::fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            static::assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].',
                $exception->getMessage()
            );
        }
    }

    public function testCanSetAnRetrieveAValueFromTheCache(): void
    {
        $context = new GenerationContext();

        $context->cacheValue('foo', $foo = new stdClass());

        static::assertSame($foo, $context->getCachedValue('foo'));
    }

    public function testCannotRetrieveAnInexistingValueFromCache(): void
    {
        $context = new GenerationContext();

        try {
            $context->getCachedValue('foo');
            static::fail('Expected exception to be thrown.');
        } catch (CachedValueNotFound $exception) {
            static::assertEquals(
                'No value with the key "foo" was found in the cache.',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNull($exception->getPrevious());
        }
    }
}
