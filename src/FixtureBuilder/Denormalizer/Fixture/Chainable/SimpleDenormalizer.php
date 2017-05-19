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
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory;

final class SimpleDenormalizer implements ChainableFixtureDenormalizerInterface, FlagParserAwareInterface
{
    use IsAServiceTrait;

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
    
    /**
     * @inheritdoc
     */
    public function withFlagParser(FlagParserInterface $parser): self
    {
        return new self($this->specsDenormalizer, $parser);
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference): bool
    {
        return false === strpos($reference, '{');
    }

    /**
     * @inheritdoc
     */
    public function denormalize(FixtureBag $builtFixtures, string $className, string $unparsedFixtureId, array $specs, FlagBag $flags): FixtureBag
    {
        if (null === $this->flagParser) {
            throw FlagParserExceptionFactory::createForExpectedMethodToBeCalledIfHasAParser(__METHOD__);
        }
        
        $idFlags = $this->flagParser->parse($unparsedFixtureId);
        $fixture = new SimpleFixture(
            $idFlags->getKey(),
            $className,
            new SpecificationBag(null, new PropertyBag(), new MethodCallBag())
        );
        $fixture = $fixture->withSpecs(
            $this->specsDenormalizer->denormalize($fixture, $this->flagParser, $specs)
        );

        return $builtFixtures->with(
            new TemplatingFixture(
                new SimpleFixtureWithFlags(
                    $fixture,
                    $idFlags->mergeWith($flags)
                )
            )
        );
    }
}
