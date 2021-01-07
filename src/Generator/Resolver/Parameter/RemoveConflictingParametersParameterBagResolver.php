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

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ParameterBag;

/**
 * Remove all the injected parameters conflicting with the existing ones to ensure the right parameter is always used.
 */
final class RemoveConflictingParametersParameterBagResolver implements ParameterBagResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var ParameterBagResolverInterface
     */
    private $resolver;

    public function __construct(ParameterBagResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }
    
    public function resolve(
        ParameterBag $unresolvedParameters,
        ParameterBag $injectedParameters = null
    ): ParameterBag {
        if (null === $injectedParameters) {
            $injectedParameters = new ParameterBag();
        }

        foreach ($injectedParameters as $injectedParameterKey => $injectedParameterValue) {
            if ($unresolvedParameters->has($injectedParameterKey)) {
                $injectedParameters = $injectedParameters->without($injectedParameterKey);
            }
        }

        return $this->resolver->resolve($unresolvedParameters, $injectedParameters);
    }
}
