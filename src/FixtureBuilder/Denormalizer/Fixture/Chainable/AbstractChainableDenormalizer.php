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
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\NotClonableTrait;

/**
 * @internal
 */
abstract class AbstractChainableDenormalizer
implements ChainableFixtureDenormalizerInterface, FixtureDenormalizerAwareInterface, FlagParserAwareInterface
{
    use NotClonableTrait;

    /**
     * @var FixtureDenormalizerInterface|null
     */
    private $denormalizer;

    /**
     * @var FlagParserInterface|null
     */
    protected $parser;

    public function __construct(FixtureDenormalizerInterface $denormalizer = null, FlagParserInterface $parser = null)
    {
        $this->denormalizer = $denormalizer;
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function withFlagParser(FlagParserInterface $parser): self
    {
        return new static($this->denormalizer, $parser);
    }

    /**
     * @inheritdoc
     */
    public function withFixtureDenormalizer(FixtureDenormalizerInterface $denormalizer)
    {
        return new static($denormalizer, $this->parser);
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
            throw DenormalizerNotFoundException::createUnexpectedCall(__METHOD__);
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
        $builtFixtures = $builtFixtures->without($tempFixture);

        return [$tempFixture, $builtFixtures];
    }

    /**
     * @param string $method
     *
     * @throws FlagParserNotFoundException
     */
    protected function checkFlagParser(string $method)
    {
        if (null === $this->parser) {
            throw FlagParserNotFoundException::createUnexpectedCall($method);
        }
    }
}
