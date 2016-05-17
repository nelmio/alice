<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter\ValueResolver;

use Nelmio\Alice\Exception\Resolver\ResolverNotFoundException;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\Parameter\ChainableParameterValueResolverInterface;
use Nelmio\Alice\Resolver\Parameter\ParameterValueResolverAwareInterface;
use Nelmio\Alice\Resolver\Parameter\ParameterValueResolverInterface;
use Nelmio\Alice\Resolver\Parameter\ResolvingCounter;

final class ArrayValueResolver implements ChainableParameterValueResolverInterface, ParameterValueResolverAwareInterface
{
    /**
     * @var ParameterValueResolverInterface|null
     */
    private $resolver;

    /**
     * @inheritdoc
     */
    public function setResolver(ParameterValueResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function canResolve($value): bool
    {
        return is_array($value);
    }

    /**
     * {@inheritdoc}
     * 
     * @param array $value
     * 
     * @throws ResolverNotFoundException
     * 
     * @return mixed
     */
    public function resolve($unresolvedArray, ParameterBag $injectedParameters = null, ResolvingCounter $resolving = null)
    {
        if (null === $this->resolver) {
            throw new ResolverNotFoundException(
                sprintf(
                    'Resolver "%s" must have a resolver set before having the method "%s" called.',
                    __CLASS__,
                    __METHOD__
                )
            );
        }

        $resolvedValue = [];
        foreach ($unresolvedArray as $index => $unresolvedValue) {
            $resolvedValue[$index] = $this->resolver->resolve($unresolvedValue, $resolving);
        }
        
        return $resolvedValue;
    }
}
