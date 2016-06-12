<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;

class FakeFixtureDenormalizer implements FixtureDenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $reference,
        array $specs,
        FlagBag $flags
    ): FixtureBag
    {
        throw new \BadMethodCallException();
    }
}
