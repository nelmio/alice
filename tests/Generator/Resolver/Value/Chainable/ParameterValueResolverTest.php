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
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver
 */
class ParameterValueResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(ParameterValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ParameterValueResolver::class))->isCloneable());
    }

    public function testCanResolveVariableValues()
    {
        $resolver = new ParameterValueResolver();

        $this->assertTrue($resolver->canResolve(new ParameterValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testReturnsParameterFromTheFixtureSet()
    {
        $value = new ParameterValue('foo');
        $set = ResolvedFixtureSetFactory::create(
            new ParameterBag(['foo' => 'bar'])
        );

        $expected = new ResolvedValueWithFixtureSet('bar', $set);

        $resolver = new ParameterValueResolver();
        $actual = $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not find the parameter "foo".
     */
    public function testThrowsAnExceptionIfTheVariableCannotBeFoundInTheScope()
    {
        $value = new ParameterValue('foo');
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new ParameterValueResolver();
        $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());
    }
}
