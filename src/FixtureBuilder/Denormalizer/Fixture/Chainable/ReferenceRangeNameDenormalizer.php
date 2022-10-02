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
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\LogicExceptionFactory;

final class ReferenceRangeNameDenormalizer implements ChainableFixtureDenormalizerInterface, FlagParserAwareInterface
{
    use IsAServiceTrait;

    private const REGEX = '/.+\{(?<expression>@(?<name>([A-Za-z0-9-_\.]+))(?<flag>(\*+))?)\}/';

    /**
     * @var FlagParserInterface|null
     */
    private $flagParser;

    /**
     * @var SpecificationsDenormalizerInterface
     */
    private $specsDenormalizer;

    public function __construct(SpecificationsDenormalizerInterface $specsDenormalizer, FlagParserInterface $parser = null)
    {
        $this->specsDenormalizer = $specsDenormalizer;
        $this->flagParser = $parser;
    }

    public function withFlagParser(FlagParserInterface $parser): self
    {
        return new self($this->specsDenormalizer, $parser);
    }

    public function canDenormalize(string $name, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $name, $matches);
    }

    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $fixtureId,
        array $specs,
        FlagBag $flags
    ): FixtureBag {
        $matches = [];
        if (false === $this->canDenormalize($fixtureId, $matches)) {
            throw LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer(__METHOD__);
        }

        if (null === $this->flagParser) {
            throw FlagParserExceptionFactory::createForExpectedMethodToBeCalledIfHasAParser(__METHOD__);
        }

        $referencedName = $matches['name'];
        $allFlag = ($matches['flag'] ?? null) === '*';
        $idFlags = $this->flagParser->parse($fixtureId);

        $fixtureIds = $this->buildReferencedValues($builtFixtures, $referencedName, $allFlag);

        $fixtureIdPrefix = $this->determineFixtureIdPrefix($fixtureId);

        foreach ($fixtureIds as $referencedFixtureId => $valueForCurrent) {
            if ($valueForCurrent->isATemplate()) {
                continue;
            }

            $builtFixtures = $builtFixtures->with(
                $this->buildFixture(
                    $fixtureIdPrefix . $referencedFixtureId,
                    $className,
                    $specs,
                    $idFlags->mergeWith($flags),
                    $valueForCurrent
                )
            );
        }

        return $builtFixtures;
    }

    /**
     * @return array<string, TemplatingFixture>
     */
    private function buildReferencedValues(
        FixtureBag $builtFixtures,
        string $referencedName,
        bool $allFlag
    ): array {
        if (false === $allFlag) {
            /** @var TemplatingFixture $fixture */
            $fixture = $builtFixtures->get($referencedName);

            return [$referencedName => $fixture];
        }

        /** @var array<string, TemplatingFixture> $matchedFixtures */
        $matchedFixtures = array_filter(
            $builtFixtures->toArray(),
            static function (string $referenceName) use ($referencedName) {
                return str_starts_with($referenceName, $referencedName);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (count($matchedFixtures) === 0) {
            throw FixtureNotFoundExceptionFactory::createWildcard($referencedName);
        }

        return $matchedFixtures;
    }

    private function determineFixtureIdPrefix(string $fixtureId): string
    {
        $matches = [];
        if (false === $this->canDenormalize($fixtureId, $matches)) {
            throw LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer(__METHOD__);
        }

        return str_replace(
            sprintf('{%s}', $matches['expression']),
            '',
            $matches[0]
        );
    }

    private function buildFixture(
        string $fixtureId,
        string $className,
        array $specs,
        FlagBag $flags,
        FixtureInterface $valueForCurrent
    ): FixtureInterface {
        $fixture = new SimpleFixture(
            $fixtureId,
            $className,
            new SpecificationBag(null, new PropertyBag(), new MethodCallBag()),
            $valueForCurrent
        );
        $fixture = $fixture->withSpecs(
            $this->specsDenormalizer->denormalize($fixture, $this->flagParser, $specs)
        );

        return new TemplatingFixture(
            new SimpleFixtureWithFlags(
                $fixture,
                $flags->withKey($fixtureId)
            )
        );
    }
}
