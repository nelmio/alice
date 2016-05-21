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
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

final class ArrayParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    /**
     * @var ParameterResolverInterface|null
     */
    private $resolver;

    /**
     * @inheritdoc
     */
    public function withResolver(ParameterResolverInterface $resolver): self
    {
        $clone = clone $this;
        $clone->resolver = $resolver;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        return is_array($parameter->getValue());
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResolverNotFoundException
     *
     * @return array
     */
    public function resolve(
        Parameter $unresolvedArrayParameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null
    ): ParameterBag
    {
        if (null === $this->resolver) {
            throw new ResolverNotFoundException(
                sprintf(
                    'Resolver "%s" must have a resolver set before having the method "%s::%s()" called.',
                    __CLASS__,
                    (new \ReflectionObject($this))->getShortName(),
                    __FUNCTION__
                )
            );
        }

        $resolvedArray = [];
        $resolvedParameterBag = new ParameterBag();
        /* @var array $unresolvedArray */
        $unresolvedArray = $unresolvedArrayParameter->getValue();
        foreach ($unresolvedArray as $index => $unresolvedValue) {
            // Iterate over all the values of the array to resolve each of them
            $resolvedValueBag = $this->resolver->resolve(
                new Parameter($index, $unresolvedValue),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            );

            $resolvedArray[$index] = $resolvedValueBag->get($index);
            $resolvedValueBag = $resolvedValueBag->without($index);
            
            foreach ($resolvedValueBag as $key => $value) {
                $resolvedParameterBag = $resolvedParameterBag->with(new Parameter($key, $value));
            }
        }
        $resolvedParameterBag = $resolvedParameterBag->with($unresolvedArrayParameter->withValue($resolvedArray));
        
        return $resolvedParameterBag;
    }
}
