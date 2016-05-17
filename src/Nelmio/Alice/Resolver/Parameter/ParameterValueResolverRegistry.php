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
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParameterResolverInterface;
use Nelmio\Alice\Throwable\ParseThrowable;

final class ParameterValueResolverRegistry implements ParameterValueResolverInterface
{
    /**
     * @var ParameterValueResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ParameterValueResolverInterface[] $resolvers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            if ($resolver instanceof ParameterValueResolverAwareInterface) {
                $resolver->setResolver($this);
                
                continue;
            }
            
            if (false === $resolver instanceof ParameterValueResolverInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected resolvers to be "%s" objects. Resolver "%s" is not.',
                        ParameterValueResolverInterface::class,
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
     * @param ParameterBag      $unresolvedParameters
     * @param ParameterBag|null $injectedParameters
     * @param array             $resolving
     *
     * @return ParameterBag
     */
    public function resolve(
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters = null,
        ResolvingCounter $resolving = null
    ): ParameterBag
    {
        $resolvedParameters = $injectedParameters;
        foreach ($unresolvedParameters as $key => $value) {
            if ($injectedParameters->has($key)) {
                continue;
            }
            
            $resolving = ResolvingCounter::createFrom($resolving)->with($key);
            $value = $this->resolveValue($key, $value, $injectedParameters, $resolving);

            $resolvedParameters = $resolvedParameters->with($key, $value);
        }

        return $resolvedParameters;
    }

    /**
     * @param string           $key
     * @param mixed            $value
     * @param ResolvingCounter $resolving
     *
     * @throws ResolverNotFoundException
     * @throws ParseThrowable
     *
     * @return ParameterBag
     */
    public function resolveValue(string $key, $value, ParameterBag $injectedParameters, ResolvingCounter $resolving)
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($value)) {
                return $resolver->resolve($value, $injectedParameters, $resolving);
            }
        }

        throw ResolverNotFoundException::create($key);
    }
}
