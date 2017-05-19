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

namespace Nelmio\Alice\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;

/**
 * Resolves "static" parameters, i.e. parameters that requires no further processing.
 */
final class StaticParameterResolver implements ChainableParameterResolverInterface
{
    use IsAServiceTrait;

    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        $value = $parameter->getValue();

        return null === $value || is_bool($value) || is_numeric($value) || is_object($value);
    }

    /**
     * {@inheritdoc}
     *
     * @param bool|int|float $parameter
     */
    public function resolve(Parameter $parameter, ParameterBag $unresolvedParameters, ParameterBag $resolvedParameters): ParameterBag
    {
        return $resolvedParameters->with($parameter);
    }
}
