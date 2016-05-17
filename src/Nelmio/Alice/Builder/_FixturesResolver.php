<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

use Nelmio\Alice\Exception\Builder\CircularReferenceException;
use Nelmio\Alice\Exception\FixtureNotFound;
use Nelmio\Alice\Fixture\ResolvedFixture;
use Nelmio\Alice\Fixture\ResolvedFixtureBag;
use Nelmio\Alice\Fixture\Specifications;
use Nelmio\Alice\Fixture\UnresolvedFixtureBag;
use Nelmio\Alice\Fixture\UnresolvedFixture;

/**
 * @inheritdoc
 * @final
 */
class FixturesResolver
{
    /**
     * @var UnresolvedFixtureBag
     */
    private $unresolvedFixtures;

    /**
     * @var ResolvedFixtureBag
     */
    private $resolvedFixtures;

    public function __construct(UnresolvedFixtureBag $unresolvedFixtures)
    {
        $this->unresolvedFixtures = $unresolvedFixtures;
        $this->resolvedFixtures = new ResolvedFixtureBag();
        $this->templates = new ResolvedFixtureBag();
    }

    public function resolve(): ResolvedFixtureBag
    {
        $unresolvedFixtures = $this->unresolvedFixtures->toArray();
        foreach ($unresolvedFixtures as $unresolvedFixture) {
            if ($this->hasBeenResolved($unresolvedFixture)) {
                continue;
            }

            if ($unresolvedFixture->isTemplate()) {
                $this->templates = $this->templates->with(
                    $this->resolveFixture($unresolvedFixture)
                );

                continue;
            }

            $this->resolvedFixtures = $this->resolvedFixtures->with(
                $this->resolveFixture($unresolvedFixture)
            );
        }
    }

    private function resolveFixture(UnresolvedFixture $unresolvedFixture, array $beingResolved = []): ResolvedFixture
    {
        if (isset($beingResolved[$unresolvedFixture->getName()])) {
            throw new CircularReferenceException(
                sprintf(
                    'Circular reference found while resolving the fixtures "%s".',
                    implode('", "', $beingResolved)
                )
            );
        }
        $fixture = $this->createResolvedFixture($unresolvedFixture);

        foreach ($unresolvedFixture->getExtends() as $extend) {
            if ($this->templates->has($extend)) {
                $extendedFixture = $this->templates->get($extend);
                $fixture->extend($extendedFixture);

                continue;
            }

            if (false === $this->unresolvedFixtures->has($extend)) {
                throw new FixtureNotFound(
                    sprintf(
                        'Fixture "%s" extends "%s" but could not find it.',
                        $unresolvedFixture->getName(),
                        $extend
                    )
                );
            }

            $unresolvedExtendedFixture = $this->unresolvedFixtures->get($extend);
            $beingResolved[$unresolvedFixture->getName()] = true;

            $extendedFixture = $this->resolveFixture($unresolvedExtendedFixture, $beingResolved);
            $this->resolvedFixtures = $this->resolvedFixtures->with($extendedFixture);

            $fixture->extend($extendedFixture);
        }

        return $fixture;
    }

    private function createResolvedFixture(UnresolvedFixture $unresolvedFixture): ResolvedFixture
    {
        $specs = $unresolvedFixture->getSpecs();
        $constructor = $specs['__construct']?? null;
        unset($specs['__construct']);

        $calls = $specs['__calls']?? [];
        unset($specs['__calls']);

        $resolvedSpecs = Specifications::create(
            $constructor,
            $specs,
            $calls
        );

        return new ResolvedFixture(
            $unresolvedFixture->getClassName(),
            $unresolvedFixture->getName(),
            $resolvedSpecs,
            $unresolvedFixture->getValueForCurrent()
        );
    }

    private function hasBeenResolved(UnresolvedFixture $fixture): bool
    {
        return $this->resolvedFixtures->has($fixture->getName()) || $this->templates->has($fixture->getName());
    }
}
