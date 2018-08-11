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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver
 */
class EvaluatedValueResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(EvaluatedValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(EvaluatedValueResolver::class))->isCloneable());
    }

    public function testCanResolveFixtureReferenceValues()
    {
        $resolver = new EvaluatedValueResolver();

        $this->assertTrue($resolver->canResolve(new EvaluatedValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testEvaluateTheGivenExpression()
    {
        $value = new EvaluatedValue('"Hello"." "."world!"');
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $set = ResolvedFixtureSetFactory::create();

        $expected = new ResolvedValueWithFixtureSet(
            'Hello world!',
            $set
        );

        $resolver = new EvaluatedValueResolver();
        $actual = $resolver->resolve($value, $fixture, $set, [], new GenerationContext());

        $this->assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfInvalidExpression()
    {
        try {
            $value = new EvaluatedValue('"unclosed string');
            $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
            $set = ResolvedFixtureSetFactory::create();

            $resolver = new EvaluatedValueResolver();
            $resolver->resolve($value, $fixture, $set, [], new GenerationContext());

            $this->fail('Expected exception to be thrown.');
        } catch (UnresolvableValueException $exception) {
            $this->assertEquals(
                'Could not evaluate the expression ""unclosed string": syntax error, unexpected end of file, expecting variable (T_VARIABLE) or ${ (T_DOLLAR_OPEN_CURLY_BRACES) or {$ (T_CURLY_OPEN)',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNotNull($exception->getPrevious());
        }
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not evaluate the expression "(function () { throw new \Exception(""); })()".
     */
    public function testThrowsAnExceptionIfAnErrorOccurredDuringEvaluation()
    {
        $value = new EvaluatedValue('(function () { throw new \\Exception(""); })()');
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new EvaluatedValueResolver();
        $resolver->resolve($value, $fixture, $set, [], new GenerationContext());
    }

    public function testTheEvaluatedExpressionCanContainScopeFunctions()
    {
        $value = new EvaluatedValue('$foo');
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $set = ResolvedFixtureSetFactory::create();
        $scope = [
            'foo' => 'bar',
        ];

        $expected = new ResolvedValueWithFixtureSet(
            'bar',
            $set
        );

        $resolver = new EvaluatedValueResolver();
        $actual = $resolver->resolve($value, $fixture, $set, $scope, new GenerationContext());

        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox The only variables the evaluated function has access to are "private" variables and the scope variables.
     */
    public function testVariablesInference()
    {
        $value = new EvaluatedValue('["foo" => $foo, "expression" => $_expression, "scope" => $_scope]');
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $set = ResolvedFixtureSetFactory::create();
        $scope = [
            'foo' => 'bar',
        ];

        $expected = new ResolvedValueWithFixtureSet(
            [
                'foo' => 'bar',
                'expression' => '["foo" => $foo, "expression" => $_expression, "scope" => $_scope]',
                'scope' => $scope,
            ],
            $set
        );

        $resolver = new EvaluatedValueResolver();
        $actual = $resolver->resolve($value, $fixture, $set, $scope, new GenerationContext());

        $this->assertEquals($expected, $actual);
        $this->assertSame(['foo' => 'bar'], $scope);

        $value = new EvaluatedValue('$scope');
        try {
            $resolver->resolve($value, $fixture, $set, $scope, new GenerationContext());
            $this->fail('Expected an exception to be thrown.');
        } catch (UnresolvableValueException $exception) {
            $this->assertEquals(
                'Could not evaluate the expression "$scope": Undefined variable: scope',
                $exception->getMessage()
            );
        }
    }

    public function testVariablesInferenceWithCurrent()
    {
        $value = new EvaluatedValue('["foo" => $foo, "expression" => $_expression, "scope" => $_scope]');
        $fixture = new SimpleFixture('dummy_1', 'Dummy', SpecificationBagFactory::create(), '1');
        $set = ResolvedFixtureSetFactory::create();
        $scope = [
            'foo' => 'bar',
        ];

        $expected = new ResolvedValueWithFixtureSet(
            [
                'foo' => 'bar',
                'expression' => '["foo" => $foo, "expression" => $_expression, "scope" => $_scope]',
                'scope' => [
                    'foo' => 'bar',
                    'current' => '1',
                ],
            ],
            $set
        );

        $resolver = new EvaluatedValueResolver();
        $actual = $resolver->resolve($value, $fixture, $set, $scope, new GenerationContext());

        $this->assertEquals($expected, $actual);

        $value = new EvaluatedValue('$scope');
        try {
            $resolver->resolve($value, $fixture, $set, $scope, new GenerationContext());
            $this->fail('Expected an exception to be thrown.');
        } catch (UnresolvableValueException $exception) {
            $this->assertEquals(
                'Could not evaluate the expression "$scope": Undefined variable: scope',
                $exception->getMessage()
            );
        }
    }
}
