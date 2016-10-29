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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureBuilder\BareFixtureSet;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeDenormalizer implements DenormalizerInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function denormalize(array $data): BareFixtureSet
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
