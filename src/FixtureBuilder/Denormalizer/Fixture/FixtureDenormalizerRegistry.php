<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerNotFoundException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\NotClonableTrait;

final class FixtureDenormalizerRegistry implements FixtureDenormalizerInterface
{
    use NotClonableTrait;
    
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
                throw new \TypeError(
                    sprintf(
                        'Expected denormalizer %d to be a "%s", got "%s" instead.',
                        $index,
                        ChainableFixtureDenormalizerInterface::class,
                        is_object($denormalizer) ? get_class($denormalizer) : gettype($denormalizer)
                    )
                );
            }
            
            if ($denormalizer instanceof FlagParserAwareInterface) {
                $this->denormalizers[] = $denormalizer->withParser($flagParser);
                
                continue;
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

        throw new FixtureDenormalizerNotFoundException(
            sprintf(
                'No suitable fixture denormalizer found to handle the fixture with the reference "%s".',
                $fixtureId
            )
        );
    }
}
