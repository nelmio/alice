<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureBuilder\BareFixtureSet;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;

class FakeDenormalizer implements DenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function denormalize(array $data): BareFixtureSet
    {
        throw new \BadMethodCallException();
    }
}
