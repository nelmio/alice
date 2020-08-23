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

use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;

/**
 * Decorates a simple parameter resolver to resolve a bag.
 */
final class SimpleParameterBagResolver implements ParameterBagResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var ParameterResolverInterface
     */
    private $resolver;

    public function __construct(ParameterResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }
    
    public function resolve(
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters = null
    ): ParameterBag {
        $resolvedParameters = $injectedParameters ?? new ParameterBag();
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
