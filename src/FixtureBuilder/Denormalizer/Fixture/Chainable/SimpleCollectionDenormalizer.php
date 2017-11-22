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
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\InvalidScopeException;

/**
 * Decorates a collection denormalizer to do the denormalization. If the denormalization fails due to an invalid scope,
 * this is likely due to the usage of a temporary fixture with a unique value. In that situation, this denormalizer
 * tries to fallback on denormalizing the fixtures one by one with the correct ID.
 */
final class SimpleCollectionDenormalizer implements CollectionDenormalizer, FixtureDenormalizerAwareInterface, FlagParserAwareInterface
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
        if ($decoratedCollectionDenormalizer instanceof FixtureDenormalizerAwareInterface
            && null !== $decoratedDenormalizer
        ) {
            $decoratedCollectionDenormalizer = $decoratedCollectionDenormalizer->withFixtureDenormalizer($decoratedDenormalizer);
        }

        if ($decoratedCollectionDenormalizer instanceof FlagParserAwareInterface
            && null !== $parser
        ) {
            $decoratedCollectionDenormalizer = $decoratedCollectionDenormalizer->withFlagParser($parser);
        }

        $this->collectionDenormalizer = $decoratedCollectionDenormalizer;
        $this->denormalizer = $decoratedDenormalizer;
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function withFlagParser(FlagParserInterface $parser): self
    {
        return new static($this->collectionDenormalizer, $this->denormalizer, $parser);
    }

    /**
     * @inheritdoc
     */
    public function withFixtureDenormalizer(FixtureDenormalizerInterface $denormalizer)
    {
        return new static($this->collectionDenormalizer, $denormalizer, $this->parser);
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference): bool
    {
        return $this->collectionDenormalizer->canDenormalize($reference);
    }

    /**
     * @inheritdoc
     */
    public function buildIds(string $id): array
    {
        return $this->collectionDenormalizer->buildIds($id);
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
        try {
            return $this->collectionDenormalizer->denormalize($builtFixtures, $className, $fixtureId, $specs, $flags);
        } catch (InvalidScopeException $exception) {
            // Continue to fallback on a more conventional way
        }

        if (null === $this->denormalizer) {
            throw DenormalizerExceptionFactory::createDenormalizerNotFoundUnexpectedCall(__METHOD__);
        }

        if (null === $this->parser) {
            throw FlagParserExceptionFactory::createForExpectedMethodToBeCalledIfHasAParser(__METHOD__);
        }

        $flags = $this->parser->parse($fixtureId)->mergeWith($flags, false);
        $fixtureId = $flags->getKey();

        $fixtureIds = $this->buildIds($fixtureId);
        foreach ($fixtureIds as $fixtureId => $valueForCurrent) {
            $builtFixtures = $this->denormalizeFixture(
                $builtFixtures,
                $className,
                $fixtureId,
                $specs,
                $flags,
                (string) $valueForCurrent
            );
        }

        return $builtFixtures;
    }

    final private function denormalizeFixture(
        FixtureBag $builtFixtures,
        string $className,
        string $fixtureId,
        array $specs,
        FlagBag $flags,
        string $valueForCurrent
    ): FixtureBag {
        $builtFixtures = $this->denormalizer->denormalize(
            $builtFixtures,
            $className,
            $fixtureId,
            $specs,
            $flags
        );

        // At this point we remove the denormalized fixture to re-create a new one with this time its value for current
        // set. The process is a bit awkward, but this is due to the fact that the value for current cannot be passed
        // to the decorated denormalizers in a simple way.

        $builtFixture = $builtFixtures->get($fixtureId);
        $builtFixtures = $builtFixtures->without($builtFixture);

        return $builtFixtures->with(
            new TemplatingFixture(
                new SimpleFixtureWithFlags(
                    new SimpleFixture(
                        $fixtureId,
                        $builtFixture->getClassName(),
                        $builtFixture->getSpecs(),
                        $valueForCurrent
                    ),
                    $flags->withKey($fixtureId)
                )
            )
        );
    }
}
