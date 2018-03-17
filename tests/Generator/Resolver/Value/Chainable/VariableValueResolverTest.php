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
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver
 */
class VariableValueResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(VariableValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(VariableValueResolver::class))->isCloneable());
    }

    public function testCanResolveVariableValues()
    {
        $resolver = new VariableValueResolver();

        $this->assertTrue($resolver->canResolve(new VariableValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testGetsTheVariableFromTheScope()
    {
        $value = new VariableValue('ping');
        $set = ResolvedFixtureSetFactory::create(
            new ParameterBag(['foo' => 'bar'])
        );
        $scope = ['ping' => 'pong'];

        $expected = new ResolvedValueWithFixtureSet('pong', $set);

        $resolver = new VariableValueResolver();
        $actual = $resolver->resolve($value, new FakeFixture(), $set, $scope, new GenerationContext());

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not find a variable "$foo".
     */
    public function testThrowsAnExceptionIfTheVariableCannotBeFoundInTheScope()
    {
        $value = new VariableValue('foo');
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new VariableValueResolver();
        $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());
    }
}
