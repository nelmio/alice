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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;

class ResolvedFixtureSetFactory
{
    public static function create(ParameterBag $parameters = null, FixtureBag $fixtures = null, ObjectBag $objects = null): ResolvedFixtureSet
    {
        return new ResolvedFixtureSet(
            (null === $parameters) ? new ParameterBag() : $parameters,
            (null === $fixtures) ? new FixtureBag() : $fixtures,
            (null === $objects) ? new ObjectBag() : $objects
        );
    }
}
