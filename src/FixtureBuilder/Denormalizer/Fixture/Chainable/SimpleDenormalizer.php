<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\Fixture\FixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\NotClonableTrait;

final class SimpleDenormalizer implements ChainableFixtureDenormalizerInterface, FlagParserAwareInterface
{
    use NotClonableTrait;

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
    public function withParser(FlagParserInterface $parser): self
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
        $this->checkFlagParser();
        
        $idFlags = $this->flagParser->parse($unparsedFixtureId);
        $fixture = new SimpleFixture(
            $idFlags->getKey(),
            $className,
            new SpecificationBag(null, new PropertyBag(), new MethodCallBag())
        );
        $fixture = $fixture->withSpecs(
            $this->specsDenormalizer->denormalizer($fixture, $this->flagParser, $specs)
        );

        return $builtFixtures->with(
            new FixtureWithFlags(
                $fixture,
                $idFlags->mergeWith($flags)
            )
        );
    }

    private function checkFlagParser()
    {
        if (null === $this->flagParser) {
            throw new \LogicException(
                sprintf(
                    'Expected method "%s" to be called only if it has a flag parser.',
                    __METHOD__
                )
            );
        }
    }
}
