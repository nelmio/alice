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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory;

/**
 * Decorates a collection denormalizer to determine which fixtures it can build and how to build the fixture IDs, e.g.
 * to know if it can build a fixture with the ID 'dummy{1..2}' and to generate the 'dummy1' and 'dummy2' IDs.
 *
 * To instantiate the fixtures, it chooses the strategy to instantiate a "temporary" fixture, and then creates the real
 * fixtures from this temporary fixture to only have to generate a fixture 1 time instead of X times.
 */
final class CollectionDenormalizerWithTemporaryFixture implements CollectionDenormalizer, FixtureDenormalizerAwareInterface, FlagParserAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var FixtureDenormalizerInterface|null
     */
    private $denormalizer;

    /**
     * @var FlagParserInterface|null
     */
    private $parser;

    /**
     * @var CollectionDenormalizer
     */
    private $collectionDenormalizer;

    public function __construct(
        CollectionDenormalizer $decoratedCollectionDenormalizer,
        FixtureDenormalizerInterface $decoratedDenormalizer = null,
        FlagParserInterface $parser = null
    ) {
        $this->collectionDenormalizer = $decoratedCollectionDenormalizer;
        $this->denormalizer = $decoratedDenormalizer;
        $this->parser = $parser;
    }
    
    public function withFlagParser(FlagParserInterface $parser): self
    {
        return new static($this->collectionDenormalizer, $this->denormalizer, $parser);
    }
    
    public function withFixtureDenormalizer(FixtureDenormalizerInterface $denormalizer)
    {
        return new static($this->collectionDenormalizer, $denormalizer, $this->parser);
    }
    
    public function canDenormalize(string $reference): bool
    {
        return $this->collectionDenormalizer->canDenormalize($reference);
    }
    
    public function buildIds(string $id): array
    {
        return $this->collectionDenormalizer->buildIds($id);
    }
    
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $fixtureId,
        array $specs,
        FlagBag $flags
    ): FixtureBag {
        if (null === $this->denormalizer) {
            throw DenormalizerExceptionFactory::createDenormalizerNotFoundUnexpectedCall(__METHOD__);
        }

        if (null === $this->parser) {
            throw FlagParserExceptionFactory::createForExpectedMethodToBeCalledIfHasAParser(__METHOD__);
        }

        $flags = $this->parser->parse($fixtureId)->mergeWith($flags, false);
        $fixtureId = $flags->getKey();

        /**
         * @var FixtureInterface $tempFixture
         * @var FixtureBag       $builtFixtures
         */
        [$tempFixture, $builtFixtures] = $this->denormalizeTemporaryFixture(
            $builtFixtures,
            $className,
            $specs,
            $flags
        );

        $fixtureIds = $this->buildIds($fixtureId);
        foreach ($fixtureIds as $fixtureId => $valueForCurrent) {
            $builtFixtures = $builtFixtures->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            $fixtureId,
                            $tempFixture->getClassName(),
                            $tempFixture->getSpecs(),
                            (string) $valueForCurrent
                        ),
                        $flags->withKey($fixtureId)
                    )
                )
            );
        }

        return $builtFixtures;
    }

    /**
     * Helper method which uses the denormalizer to denormalize a fixture with the given properties but with a random
     * ID. The ID used and with the fixtures are returned.
     *
     * This helper is used to optimize the number of call made on the decorated denormalizer: instead of building the
     * IDs from the list or the range, and then denormalizing as many time as needed, the denormalization is done only
     * once.
     */
    private function denormalizeTemporaryFixture(
        FixtureBag $builtFixtures,
        string $className,
        array $specs,
        FlagBag $flags
    ): array {
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
}
