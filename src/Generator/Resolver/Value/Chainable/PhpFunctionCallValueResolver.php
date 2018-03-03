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

use Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;

final class PhpFunctionCallValueResolver implements ChainableValueResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    /**
     * @var array
     */
    private $functionBlacklist;

    /**
     * @param string[]               $functionBlacklist List of PHP native function that will be skipped, i.e. will be
     *                                                  considered as non existent
     */
    public function __construct(array $functionBlacklist, ValueResolverInterface $decoratedResolver)
    {
        $this->functionBlacklist = array_flip($functionBlacklist);
        $this->resolver = $decoratedResolver;
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof ResolvedFunctionCallValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param ResolvedFunctionCallValue $value
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        $functionName = $value->getName();
        if (false === function_exists($functionName)
            || array_key_exists($functionName, $this->functionBlacklist)
        ) {
            return $this->resolver->resolve($value, $fixture, $fixtureSet, $scope, $context);
        }

        $arguments = $value->getArguments();

        return new ResolvedValueWithFixtureSet(
            $functionName(...$arguments),
            $fixtureSet
        );
    }
}
