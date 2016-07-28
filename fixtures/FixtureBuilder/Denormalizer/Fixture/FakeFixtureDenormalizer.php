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
use Nelmio\Alice\NotCallableTrait;

class FakeFixtureDenormalizer implements FixtureDenormalizerInterface
{
    use NotCallableTrait;

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
        $this->__call(__FUNCTION__, func_get_args());
    }
}
