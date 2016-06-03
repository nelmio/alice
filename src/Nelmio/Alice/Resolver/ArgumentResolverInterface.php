<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver;

use Nelmio\Alice\Fixture\Argument;
use Nelmio\Alice\FixtureResolutionResult;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\Fixture\ResolvingContext;
use Nelmio\Alice\UnresolvedFixtureBag;

interface ArgumentResolverInterface
{
    public function resolve(
        Argument $argument,
        ParameterBag $parameters,
        UnresolvedFixtureBag $fixtures,
        ObjectBag $objects,
        ResolvingContext $context
    ): FixtureResolutionResult;
}
