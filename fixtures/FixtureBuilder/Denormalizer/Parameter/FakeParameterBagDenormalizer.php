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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter;

use Nelmio\Alice\FixtureBuilder\Denormalizer\ParameterBagDenormalizerInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeParameterBagDenormalizer implements ParameterBagDenormalizerInterface
{
    use NotCallableTrait;

    public function denormalize(array $data): never
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
