<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\Fixture\Chainable\DenormalizerNotFoundException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\NotClonableTrait;

abstract class AbstractChainableDenormalizer implements ChainableFixtureDenormalizerInterface, FixtureDenormalizerAwareInterface
{
    use NotClonableTrait;

    /**
     * @var FixtureDenormalizerInterface|null
     */
    private $denormalizer;

    public function __construct(FixtureDenormalizerInterface $denormalizer = null)
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * @inheritdoc
     */
    public function with(FixtureDenormalizerInterface $denormalizer)
    {
        return new static($denormalizer);
    }

    /**
     * Helper method which uses the denormalizer to denormalize a fixture with the given properties but with a random
     * ID. The ID used and with the fixtures are returned.
     *
     * This helper is used to optimize the number of call made on the decorated denormalizer: instead of building the
     * IDs from the list or the range, and then denormalizing as many time as needed, the denormalization is done only
     * once.
     */
    protected function denormalizeTemporaryFixture(
        FixtureBag $builtFixtures,
        string $className,
        array $specs,
        FlagBag $flags
    ): array
    {
        if (null === $this->denormalizer) {
            throw DenormalizerNotFoundException::create(__METHOD__);
        }

        $tempFixtureId = uniqid('temporary_id');
        $builtFixtures = $this->denormalizer->denormalize(
            $builtFixtures,
            $className,
            $tempFixtureId,
            $specs,
            $flags
        );

        $tempFixture = $builtFixtures->get($tempFixtureId);
        $builtFixtures = $builtFixtures->without($tempFixtureId);

        return [$tempFixture, $builtFixtures];
    }
}
