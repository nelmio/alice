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

namespace Nelmio\Alice;

class FixtureSetFactory
{
    public static function create(
        ParameterBag $loadedParameters = null,
        ParameterBag $injectedParameters = null,
        FixtureBag $fixtures = null,
        ObjectBag $objects = null
    ): FixtureSet {
        return new FixtureSet(
            null === $loadedParameters ? new ParameterBag() : $loadedParameters,
            null === $injectedParameters ? new ParameterBag() : $injectedParameters,
            null === $fixtures ? new FixtureBag() : $fixtures,
            null === $objects ? new ObjectBag() : $objects
        );
    }
}
