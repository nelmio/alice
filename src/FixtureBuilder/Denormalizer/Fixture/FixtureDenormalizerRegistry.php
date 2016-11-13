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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;

final class FixtureDenormalizerRegistry implements FixtureDenormalizerInterface
{
    use IsAServiceTrait;
    
    /**
     * @var ChainableFixtureDenormalizerInterface[]
     */
    private $denormalizers = [];

    /**
     * @param FlagParserInterface                     $flagParser
     * @param ChainableFixtureDenormalizerInterface[] $denormalizers
     */
    public function __construct(FlagParserInterface $flagParser, array $denormalizers)
    {
        foreach ($denormalizers as $index => $denormalizer) {
            if (false === $denormalizer instanceof ChainableFixtureDenormalizerInterface) {
                throw TypeErrorFactory::createForInvalidDenormalizerType($index, $denormalizer);
            }

            if ($denormalizer instanceof FixtureDenormalizerAwareInterface) {
                $denormalizer = $denormalizer->withFixtureDenormalizer($this);
            }
            
            if ($denormalizer instanceof FlagParserAwareInterface) {
                $denormalizer = $denormalizer->withFlagParser($flagParser);
            }
            
            $this->denormalizers[] = $denormalizer;
        }
    }

    /**
     * @inheritdoc
     */
    public function denormalize(FixtureBag $builtFixtures, string $className, string $fixtureId, array $specs, FlagBag $flags): FixtureBag
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->canDenormalize($fixtureId)) {
                return $denormalizer->denormalize($builtFixtures, $className, $fixtureId, $specs, $flags);
            }
        }

        throw DenormalizerNotFoundException::createForFixture($fixtureId);
    }
}
