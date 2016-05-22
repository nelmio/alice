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

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParameterBagResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

final class ParameterResolverDecorator implements ParameterBagResolverInterface
{
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
            $resolvedValues = $this->resolver->resolve(
                new Parameter($key, $value),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            );

            foreach ($resolvedValues as $keyOfResolvedValue => $resolvedValue) {
                $resolvedParameters = $resolvedParameters->with(new Parameter($keyOfResolvedValue, $resolvedValue));
            }
        }

        return $resolvedParameters;
    }

    public function __clone()
    {
        $this->resolver = clone $this->resolver;
    }
}
