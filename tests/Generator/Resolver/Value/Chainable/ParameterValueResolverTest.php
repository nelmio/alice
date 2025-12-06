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

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(ParameterValueResolver::class)]
final class ParameterValueResolverTest extends TestCase
{
    public function testIsAChainableResolver(): void
    {
        self::assertTrue(is_a(ParameterValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ParameterValueResolver::class))->isCloneable());
    }

    public function testCanResolveVariableValues(): void
    {
        $resolver = new ParameterValueResolver();

        self::assertTrue($resolver->canResolve(new ParameterValue('')));
        self::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testReturnsParameterFromTheFixtureSet(): void
    {
        $value = new ParameterValue('foo');
        $set = ResolvedFixtureSetFactory::create(
            new ParameterBag(['foo' => 'bar']),
        );

        $expected = new ResolvedValueWithFixtureSet('bar', $set);

        $resolver = new ParameterValueResolver();
        $actual = $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfTheVariableCannotBeFoundInTheScope(): void
    {
        $value = new ParameterValue('foo');
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new ParameterValueResolver();

        $this->expectException(UnresolvableValueException::class);
        $this->expectExceptionMessage('Could not find the parameter "foo".');

        $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());
    }
}
