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

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FixtureBagDenormalizerInterface;

class FakeFixtureBagDenormalizer implements FixtureBagDenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function denormalize(array $data): FixtureBag
    {
        throw new \BadMethodCallException();
    }
}
