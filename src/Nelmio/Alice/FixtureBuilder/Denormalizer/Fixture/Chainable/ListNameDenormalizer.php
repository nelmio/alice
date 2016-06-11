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
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;

final class ListNameDenormalizer implements ChainableFixtureDenormalizerInterface, FixtureDenormalizerAwareInterface
{
    /**
     * @var FixtureDenormalizerInterface|null
     */
    private $denormalizer;

    /**
     * @inheritdoc
     */
    public function with(FixtureDenormalizerInterface $denormalizer)
    {
        $clone = clone $this;
        $clone->denormalizer = $denormalizer;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference, array &$matches = []): bool
    {
        return 1 === preg_match('/(?<prefix>.+)(\{(?<list>[^,]+(?:\s*,\s*[^,]+)*)\})(?:.*)/', $reference, $matches);
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $reference,
        array $specs,
        FlagBag $flags): FixtureBag
    {
        if (null === $this->denormalizer) {
            throw new \BadMethodCallException(
                sprintf(
                    'Expected method "%s" to be called only if it has a denormalizer.',
                    __METHOD__
                )
            );
        }
        
        $references = $this->getReferences($reference);
        foreach ($references as $builtReference) {
            $builtFixtures = $builtFixtures->mergeWith( 
                $this->denormalizer->denormalize($builtFixtures, $className, $builtReference, $specs, $flags)
            );
        }

        return $builtFixtures;
    }

    /**
     * @param string $reference
     *
     * @throws \BadMethodCallException
     *
     * @return string[]
     *
     * @example
     *  'user_{alice, bob} (template)' => [
     *      'user_alice (template)',
     *      'user_bob (template)',
     *  ]
     */
    private function getReferences(string $reference): array
    {
        if (false === $this->canDenormalize($reference, $matches = [])) {
            throw new \BadMethodCallException(
                sprintf(
                    'As a chainable builder, "%s" should be called only if "::canBuild() returns true. Got false instead.',
                    __METHOD__
                )
            );
        }
        $listElements = str_split('/\s*,\s*/', $matches['list']);

        $references = [];
        foreach ($listElements as $element) {
            $references[] = str_replace(
                sprintf('{%s}', $matches['list']),
                $element,
                $reference
            );
        }

        return $references;
    }
}
