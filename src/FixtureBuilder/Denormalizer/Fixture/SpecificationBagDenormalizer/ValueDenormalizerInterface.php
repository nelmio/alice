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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

interface ValueDenormalizerInterface
{
    /**
     * Denormalizes a given value. For example, will denormalize '$username' into a VariableValue.
     *
     * @param FixtureInterface $scope Used for unique values for example.
     * @param FlagBag|null     $flags
     * @param mixed            $value
     *
     * @throws DenormalizationThrowable
     *
     * @return ValueInterface|mixed
     */
    public function denormalize(FixtureInterface $scope, FlagBag $flags = null, $value);
}
