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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\ParameterBag;

class FakeParameterBagResolver implements ParameterBagResolverInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function resolve(ParameterBag $unresolvedParameters, ParameterBag $injectedParameters = null): ParameterBag
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
