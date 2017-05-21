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

/**
 * @covers \Nelmio\Alice\Generator\GenerationContext
 */
class GenerationContextTest extends TestCase
{
    public function testAccessors()
    {
        $context = new GenerationContext();
        $this->assertTrue($context->isFirstPass());
        $this->assertFalse($context->needsCompleteGeneration());

        $context->setToSecondPass();
        $this->assertFalse($context->isFirstPass());
        $this->assertFalse($context->needsCompleteGeneration());
        $this->assertFalse($context->needsCallResult());

        $context->markAsNeedsCompleteGeneration();
        $this->assertFalse($context->isFirstPass());
        $this->assertTrue($context->needsCompleteGeneration());
        $this->assertFalse($context->needsCallResult());

        $context->unmarkAsNeedsCompleteGeneration();
        $this->assertFalse($context->isFirstPass());
        $this->assertFalse($context->needsCompleteGeneration());
        $this->assertFalse($context->needsCallResult());

        $context->markRetrieveCallResult();
        $this->assertFalse($context->isFirstPass());
        $this->assertFalse($context->needsCompleteGeneration());
        $this->assertTrue($context->needsCallResult());

        $context->unmarkRetrieveCallResult();
        $this->assertFalse($context->isFirstPass());
        $this->assertFalse($context->needsCompleteGeneration());
        $this->assertFalse($context->needsCallResult());
    }

    public function testThrowsAnExceptionWhenACircularReferenceIsDetected()
    {
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');
        $context->markIsResolvingFixture('foo');

        try {
            $context->markIsResolvingFixture('foo');
            $this->fail('Expected exception to be thrown.');
        } catch (CircularReferenceException $exception) {
            $this->assertEquals(
                'Circular reference detected for the parameter "foo" while resolving ["bar", "foo"].',
                $exception->getMessage()
            );
        }
    }

    public function testCanSetAnRetrieveAValueFromTheCache()
    {
        $context = new GenerationContext();

        $context->cacheValue('foo', $foo = new \stdClass());

        $this->assertSame($foo, $context->getCachedValue('foo'));
    }

    public function testCannotRetrieveAnInexistingValueFromCache()
    {
        $context = new GenerationContext();

        try {
            $context->getCachedValue('foo');
            $this->fail('Expected exception to be thrown.');
        } catch (CachedValueNotFound $exception) {
            $this->assertEquals(
                'No value with the key "foo" was found in the cache.',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }
}
