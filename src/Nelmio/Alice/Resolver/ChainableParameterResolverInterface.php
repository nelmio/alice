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

use Nelmio\Alice\Parameter;

interface ChainableParameterResolverInterface extends ParameterResolverInterface
{
    public function canResolve(Parameter $parameter): bool;
}
