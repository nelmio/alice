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

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver
 */
class StaticParameterResolverTest extends TestCase
{
    public function testIsAChainableParameterResolver(): void
    {
        static::assertTrue(is_a(StaticParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(StaticParameterResolver::class))->isCloneable());
    }

    public function testCanOnlyResolveSimpleValues(): void
    {
        $resolver = new StaticParameterResolver();
        $parameter = new Parameter('foo', null);

        static::assertTrue($resolver->canResolve($parameter->withValue(null)));
        static::assertTrue($resolver->canResolve($parameter->withValue(10)));
        static::assertTrue($resolver->canResolve($parameter->withValue(.75)));
        static::assertTrue($resolver->canResolve($parameter->withValue(new stdClass())));
        static::assertTrue($resolver->canResolve($parameter->withValue(function (): void {
        })));

        static::assertFalse($resolver->canResolve($parameter->withValue('string')));
    }

    public function testReturnsResolvedParameter(): void
    {
        $parameter = new Parameter('foo', null);
        $resolver = new StaticParameterResolver();

        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        static::assertEquals(
            new ParameterBag([
                'foo' => null,
            ]),
            $result
        );
    }
}
