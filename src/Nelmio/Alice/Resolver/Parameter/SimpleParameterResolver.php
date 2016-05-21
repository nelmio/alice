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
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;

final class SimpleParameterResolver implements ChainableParameterResolverInterface
{
    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        $value = $parameter->getValue();
        
        return is_bool($value) || is_numeric($value) || is_object($value);
    }

    /**
     * {@inheritdoc}
     * 
     * @param bool|int|float $parameter
     */
    public function resolve(Parameter $parameter, ParameterBag $unresolvedParameters, ParameterBag $resolvedParameters): ParameterBag
    {
        return (new ParameterBag())->with($parameter);
    }
}
