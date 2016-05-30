<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Fixture;

use Nelmio\Alice\ExpressionLanguageInterface;
use Nelmio\Alice\FixtureResolutionResult;
use Nelmio\Alice\Generator\FixtureGeneratorInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\UnresolvedFixtureBag;

final class SimpleValueResolver
{
    /**
     * @var ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * @var FixtureGeneratorInterface|null
     */
    private $generator;

    public function __construct(ExpressionLanguageInterface $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

//    /**
//     * @inheritdoc
//     */
//    public function withGenerator(FixtureGeneratorInterface $generator): self
//    {
//        $clone = clone $this;
//        $clone->generator = $generator;
//
//        return $clone;
//    }

    public function resolve(
        $value,
        ParameterBag $parameters,
        UnresolvedFixtureBag $fixtures,
        ObjectBag $objects,
        ResolvingContext $context
    ): FixtureResolutionResult
    {
        return (is_string($value))
            ? $this->expressionLanguage->evaluate($value, $parameters, $objects)
            : $value
        ;
    }
}
