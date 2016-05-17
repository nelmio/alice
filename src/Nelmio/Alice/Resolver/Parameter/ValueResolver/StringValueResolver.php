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

use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\Parameter\ChainableParameterValueResolverInterface;
use Nelmio\Alice\Resolver\Parameter\ResolvingCounter;

final class StringValueResolver implements ChainableParameterValueResolverInterface
{
    /**
     * @inheritdoc
     */
    public function canResolve($value): bool
    {
        return is_string($value);
    }

    /**
     * {@inheritdoc}
     * 
     * @param bool|int|float $value
     */
    public function resolve($value, ParameterBag $injectedParameters = null, ResolvingCounter $resolving = null)
    {
        if (1 === preg_match('/<{(?<parameter>(?(?=\{)^[\>]|.)+)}>/', $value, $match)) {
            
        }
        
        if (false === $resolving->contains($value)) {
            return $value;
        }
        
        // $value is a parameter key
    }
}
