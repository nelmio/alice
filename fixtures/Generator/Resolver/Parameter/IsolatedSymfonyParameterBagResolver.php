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
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Symfony\KernelIsolatedServiceCall;

class IsolatedSymfonyParameterBagResolver implements ParameterBagResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(ParameterBag $unresolvedParameters, ParameterBag $injectedParameters = null): ParameterBag
    {
        return KernelIsolatedServiceCall::call(
            'nelmio_alice.generator.resolver.parameter_bag',
            function (ParameterBagResolverInterface $resolver) use ($unresolvedParameters, $injectedParameters) {
                return $resolver->resolve($unresolvedParameters, $injectedParameters);
            }
        );
    }
}
