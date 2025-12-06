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
 * @internal
 */
final class GenerationContextTest extends TestCase
{
    public function testAccessors(): void
    {
        $context = new GenerationContext();
        self::assertTrue($context->isFirstPass());
        self::assertFalse($context->needsCompleteGeneration());

        $context->setToSecondPass();
        self::assertFalse($context->isFirstPass());
        self::assertFalse($context->needsCompleteGeneration());
        self::assertFalse($context->needsCallResult());

        $context->markAsNeedsCompleteGeneration();
        self::assertFalse($context->isFirstPass());
        self::assertTrue($context->needsCompleteGeneration());
        self::assertFalse($context->needsCallResult());

        $context->unmarkAsNeedsCompleteGeneration();
        self::assertFalse($context->isFirstPass());
        self::assertFalse($context->needsCompleteGeneration());
        self::assertFalse($context->needsCallResult());

        $context->markRetrieveCallResult();
        self::assertFalse($context->isFirstPass());
        self::assertFalse($context->needsCompleteGeneration());
        self::assertTrue($context->needsCallResult());

        $context->unmarkRetrieveCallResult();
        self::assertFalse($context->isFirstPass());
        self::assertFalse($context->needsCompleteGeneration());
        self::assertFalse($context->needsCallResult());
    }

    public function testThrowsAnExceptionWhenACircularReferenceIsDetected(): void
    {
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');
        $context->markIsResolvingFixture('foo');

        try {
            $context->markIsResolvingFixture('foo');
            self::fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            self::assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].',
                $exception->getMessage(),
            );
        }
    }

    public function testCanSetAnRetrieveAValueFromTheCache(): void
    {
        $context = new GenerationContext();

        $context->cacheValue('foo', $foo = new stdClass());

        self::assertSame($foo, $context->getCachedValue('foo'));
    }

    public function testCannotRetrieveAnInexistingValueFromCache(): void
    {
        $context = new GenerationContext();

        try {
            $context->getCachedValue('foo');
            self::fail('Expected exception to be thrown.');
        } catch (CachedValueNotFound $exception) {
            self::assertEquals(
                'No value with the key "foo" was found in the cache.',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertNull($exception->getPrevious());
        }
    }
}
