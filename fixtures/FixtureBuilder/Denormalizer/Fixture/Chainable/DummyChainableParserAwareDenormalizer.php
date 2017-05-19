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
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;

class DummyChainableParserAwareDenormalizer implements ChainableFixtureDenormalizerInterface, FlagParserAwareInterface
{
    /**
     * @var ChainableFixtureDenormalizerInterface
     */
    private $decoratedDenormalizer;

    /**
     * @var FlagParserInterface|null
     */
    public $parser;
    
    /**
     * @var FlagParserAwareInterface
     */
    private $decoratedFlagAware;

    public function __construct(ChainableFixtureDenormalizerInterface $decoratedDenormalizer, FlagParserAwareInterface $decoratedFlagAware)
    {
        $this->decoratedDenormalizer = $decoratedDenormalizer;
        $this->decoratedFlagAware = $decoratedFlagAware;
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference): bool
    {
        return $this->decoratedDenormalizer->canDenormalize($reference);
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
        return $this->decoratedDenormalizer->denormalize($builtFixtures, $className, $fixtureId, $specs, $flags);
    }

    /**
     * @inheritdoc
     */
    public function withFlagParser(FlagParserInterface $parser)
    {
        $this->decoratedFlagAware->withFlagParser($parser);
        $clone = clone $this;
        $clone->parser = $parser;
        
        return $clone;
    }
    
    public function __clone()
    {
        $this->decoratedDenormalizer = clone $this->decoratedDenormalizer;
        $this->decoratedFlagAware = clone $this->decoratedFlagAware;
        if (null !== $this->parser) {
            $this->parser = clone $this->parser;
        }
    }
}
