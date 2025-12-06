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
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(StaticParameterResolver::class)]
final class StaticParameterResolverTest extends TestCase
{
    public function testIsAChainableParameterResolver(): void
    {
        self::assertTrue(is_a(StaticParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StaticParameterResolver::class))->isCloneable());
    }

    public function testCanOnlyResolveSimpleValues(): void
    {
        $resolver = new StaticParameterResolver();
        $parameter = new Parameter('foo', null);

        self::assertTrue($resolver->canResolve($parameter->withValue(null)));
        self::assertTrue($resolver->canResolve($parameter->withValue(10)));
        self::assertTrue($resolver->canResolve($parameter->withValue(.75)));
        self::assertTrue($resolver->canResolve($parameter->withValue(new stdClass())));
        self::assertTrue($resolver->canResolve($parameter->withValue(
            static function (): void {
            },
        )));

        self::assertFalse($resolver->canResolve($parameter->withValue('string')));
    }

    public function testReturnsResolvedParameter(): void
    {
        $parameter = new Parameter('foo', null);
        $resolver = new StaticParameterResolver();

        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        self::assertEquals(
            new ParameterBag([
                'foo' => null,
            ]),
            $result,
        );
    }
}
