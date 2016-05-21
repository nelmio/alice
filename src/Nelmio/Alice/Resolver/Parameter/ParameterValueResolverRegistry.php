<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

use Nelmio\Alice\Exception\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

final class ParameterResolverRegistry implements ParameterResolverInterface
{
    /**
     * @var ParameterResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ParameterResolverInterface[] $resolvers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            if ($resolver instanceof ParameterResolverAwareInterface) {
                $resolver->withResolver($this);
                
                continue;
            }
            
            if (false === $resolver instanceof ParameterResolverInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected resolvers to be "%s" objects. Resolver "%s" is not.',
                        ParameterResolverInterface::class,
                        is_object($resolver)? get_class($resolver) : $resolver
                    )
                );
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
        ResolvingContext $context = null
    ): ParameterBag
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($parameter)) {
                return $resolver->resolve($parameter, $unresolvedParameters, $injectedParameters, $context);
            }
        }
        
        throw ResolverNotFoundException::create($parameter->getKey());
    }
}
