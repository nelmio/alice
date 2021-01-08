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
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;

final class ParameterResolverRegistry implements ParameterResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableParameterResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ChainableParameterResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $index => $resolver) {
            if (false === $resolver instanceof ChainableParameterResolverInterface) {
                throw TypeErrorFactory::createForInvalidChainableParameterResolver($resolver);
            }

            if ($resolver instanceof ParameterResolverAwareInterface) {
                $resolvers[$index] = $resolver->withResolver($this);
            }
        }

        $this->resolvers = $resolvers;
    }
    
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters,
        ResolvingContext $context = null
    ): ParameterBag {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($parameter)) {
                return $resolver->resolve($parameter, $unresolvedParameters, $injectedParameters, $context);
            }
        }
        
        throw ResolverNotFoundExceptionFactory::createForParameter($parameter->getKey());
    }
}
