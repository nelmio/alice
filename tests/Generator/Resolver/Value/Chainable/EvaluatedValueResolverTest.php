<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver
 */
class EvaluatedValueResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(EvaluatedValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new EvaluatedValueResolver();
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
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['val' => 'scopie'];

        $expected = new ResolvedValueWithFixtureSet(
            'Hello world!',
            $set
        );

        $resolver = new EvaluatedValueResolver();
        $actual = $resolver->resolve($value, $fixture, $set, $scope);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not evaluate the expression ""unclosed string".
     */
    public function testThrowsAnExceptionIfInvalidExpression()
    {
        $value = new EvaluatedValue('"unclosed string');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new EvaluatedValueResolver();
        $resolver->resolve($value, $fixture, $set);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not evaluate the expression "(function () { throw new \Exception(""); })()".
     */
    public function testThrowsAnExceptionIfAnErrorOccurredDuringEvaluation()
    {
        $value = new EvaluatedValue('(function () { throw new \\Exception(""); })()');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new EvaluatedValueResolver();
        $resolver->resolve($value, $fixture, $set);
    }

    public function testTheEvaluatedExpressionCanContainScopeFunctions()
    {
        $value = new EvaluatedValue('$foo');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = [
            'foo' => 'bar',
        ];

        $expected = new ResolvedValueWithFixtureSet(
            'bar',
            $set
        );

        $resolver = new EvaluatedValueResolver();
        $actual = $resolver->resolve($value, $fixture, $set, $scope);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox The only variables the evaluated function has access to are "private" variables and the scope variables.
     */
    public function testVariablesInference()
    {
        $value = new EvaluatedValue('["foo" => $foo, "expression" => $_expression, "scope" => $_scope]');
        $fixture = new FakeFixture();
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
        $actual = $resolver->resolve($value, $fixture, $set, $scope);

        $this->assertEquals($expected, $actual);

        $value = new EvaluatedValue('$scope');
        try {
            $resolver->resolve($value, $fixture, $set, $scope);
            $this->fail('Expected an exception to be thrown.');
        } catch (UnresolvableValueException $exception) {
            $this->assertEquals(
                'Could not evaluate the expression "$scope".',
                $exception->getMessage()
            );
        }
    }
}
