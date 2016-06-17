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

use Nelmio\Alice\Exception\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Generator\Resolver\ParameterResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;

final class ParameterResolverRegistry implements ParameterResolverInterface
{
    /**
     * @var ParameterResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ChainableParameterResolverInterface[] $resolvers
     *
     * @throws \TypeError
     */
    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $index => $resolver) {
            if (false === $resolver instanceof ChainableParameterResolverInterface) {
                throw new \TypeError(
                    sprintf(
                        'Expected resolvers to be "%s" objects. Got "%s" instead.',
                        ParameterResolverInterface::class,
                        is_object($resolver)? get_class($resolver) : $resolver
                    )
                );
            }

            if ($resolver instanceof ParameterResolverAwareInterface) {
                $resolvers[$index] = $resolver->withResolver($this);
            }
        }

        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResolverNotFoundException
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters,
        \Nelmio\Alice\Generator\Resolver\ParameterResolvingContext $context = null
    ): ParameterBag
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($parameter)) {
                return $resolver->resolve($parameter, $unresolvedParameters, $injectedParameters, $context);
            }
        }
        
        throw new ResolverNotFoundException(
            sprintf(
                'No suitable resolver found for the parameter "%s".',
                $parameter->getKey()
            )
        );
    }

    public function __clone()
    {
        throw new \DomainException();
    }
}
