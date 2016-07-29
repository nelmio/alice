<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;

/**
 * Decorates a simple parameter resolver to resolve a bag.
 */
final class SimpleParameterBagResolver implements ParameterBagResolverInterface
{
    use NotClonableTrait;

    /**
     * @var ParameterResolverInterface
     */
    private $resolver;

    public function __construct(ParameterResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @param ResolvingContext $context
     *
     * @return ParameterBag
     */
    public function resolve(
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters = null
    ): ParameterBag
    {
        $resolvedParameters = (null === $injectedParameters) ? new ParameterBag() : $injectedParameters;
        foreach ($unresolvedParameters as $key => $value) {
            if ($resolvedParameters->has($key)) {
                continue;
            }

            $context = new ResolvingContext($key);
            $resolvedParameters = $this->resolver->resolve(
                new Parameter($key, $value),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            );
        }

        return $resolvedParameters;
    }
}
