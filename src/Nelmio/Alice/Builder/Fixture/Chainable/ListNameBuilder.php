<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture\Chainable;

use Nelmio\Alice\Builder\Fixture\ChainableFixtureBuilderInterface;
use Nelmio\Alice\Builder\Fixture\UnresolvedFixtureBuilderAwareInterface;
use Nelmio\Alice\Builder\Fixture\UnresolvedFixtureBuilderInterface;
use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\UnresolvedFixtureBag;

final class ListNameBuilder implements ChainableFixtureBuilderInterface, UnresolvedFixtureBuilderAwareInterface
{
    /**
     * @var UnresolvedFixtureBuilderInterface|null
     */
    private $builder;

    /**
     * @param UnresolvedFixtureBuilderInterface $builder
     *
     * @return static
     */
    public function with(UnresolvedFixtureBuilderInterface $builder)
    {
        $clone = clone $this;
        $clone->builder = $builder;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function canBuild(string $reference, array &$matches = []): bool
    {
        return 1 === preg_match('/(?<prefix>.+)(\{(?<list>[^,]+(?:\s*,\s*[^,]+)*)\})(?:.*)/', $reference, $matches);
    }

    /**
     * @inheritdoc
     */
    public function build(UnresolvedFixtureBag $builtFixtures, string $className, string $reference, array $specs, FlagBag $flags): UnresolvedFixtureBag
    {
        $references = $this->getReferences($reference);

        foreach ($references as $builtReference) {
            $builtFixtures = $this->builder->build($builtFixtures, $className, $builtReference, $specs, $flags);
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
        if (false === $this->canBuild($reference, $matches = [])) {
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
