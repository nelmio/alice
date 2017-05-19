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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeChainableDenormalizerAwareDenormalizer implements ChainableFixtureDenormalizerInterface, FixtureDenormalizerAwareInterface
{
    use NotCallableTrait;

    /**
     * @var FixtureDenormalizerInterface
     */
    public $denormalizer;

    /**
     * @param FixtureDenormalizerInterface $denormalizer
     *
     * @return static
     */
    public function withFixtureDenormalizer(FixtureDenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference): bool
    {
        $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $fixtureId,
        array $specs,
        FlagBag $flags
    ): FixtureBag {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
