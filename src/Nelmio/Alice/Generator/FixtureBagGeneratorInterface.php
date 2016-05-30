<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\UnresolvedFixtureBag;

interface FixtureBagGeneratorInterface
{
    public function generate(UnresolvedFixtureBag $fixtures, ParameterBag $parameters, ObjectBag $injectedObjects): ObjectBag;
}
