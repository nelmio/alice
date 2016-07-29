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

use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;

final class ParameterResolverRegistry implements ParameterResolverInterface
{
    use NotClonableTrait;

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
     * @inheritdoc
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
        
        throw ResolverNotFoundException::createForParameter($parameter->getKey());
    }
}
