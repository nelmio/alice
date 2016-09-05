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

use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;

/**
 * Remove all the injected parameters conflicting with the existing ones to ensure the right parameter is always used.
 */
final class RemoveConflictingParametersParameterBagResolver implements ParameterBagResolverInterface
{
    use NotClonableTrait;

    /**
     * @var ParameterBagResolverInterface
     */
    private $resolver;

    public function __construct(ParameterBagResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters = null
    ): ParameterBag
    {
        foreach ($injectedParameters as $injectedParameterKey => $injectedParameterValue) {
            if ($unresolvedParameters->has($injectedParameterKey)) {
                $injectedParameters = $injectedParameters->without($injectedParameterKey);
            }
        }

        return $this->resolver->resolve($unresolvedParameters, $injectedParameters);
    }
}
