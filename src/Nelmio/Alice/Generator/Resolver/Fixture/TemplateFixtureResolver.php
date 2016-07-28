<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Exception\FixtureNotFoundException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;

final class TemplateFixtureResolver
{
    /**
     * Resolves a given fixture. The resolution of a fixture may result in the resolution of several fixtures.
     *
     * @param FixtureInterface $fixture Fixture to resolve
     * @param FixtureBag       $unresolvedFixtures
     * @param FixtureBag       $resolvedFixtures
     *
     * @param ResolvingContext $context
     *
     * @return FixtureBag
     */
    public function resolve(
        TemplatingFixture $fixture,
        FixtureBag $unresolvedFixtures,
        TemplatingFixtureBag $resolvedFixtures,
        ResolvingContext $context
    ): TemplatingFixtureBag
    {
        $context->checkForCircularReference($fixture->getId());

        if ($fixture->extendsFixtures()) {
            /**
             * @var FixtureBag           $extendedFixtures
             * @var TemplatingFixtureBag $resolvedFixtures
             */
            list($extendedFixtures, $resolvedFixtures) = $this->resolveExtendedFixtures(
                $fixture->getExtendedFixtureIds(),
                $unresolvedFixtures,
                $resolvedFixtures,
                $context
            );

            $specs = $fixture->getSpecs();
            foreach ($extendedFixtures as $extendedFixture) {
                /** @var FixtureInterface $extendedFixture */
                $specs = $specs->mergeWith($extendedFixture->getSpecs());
            }

            $fixture = $fixture
                ->withSpecs($specs)
                ->getStrippedFixture()
            ;
        }
        $resolvedFixtures = $resolvedFixtures->with($fixture);

        return $resolvedFixtures;
    }

    /**
     * @param FixtureReference[]   $extendedFixtureReferences
     * @param FixtureBag           $unresolvedFixtures
     * @param TemplatingFixtureBag $resolvedFixtures
     * @param ResolvingContext     $context
     *
     * @throws FixtureNotFoundException
     *
     * @return array<FixtureBag, TemplatingFixtureBag>
     */
    private function resolveExtendedFixtures(
        array $extendedFixtureReferences,
        FixtureBag $unresolvedFixtures,
        TemplatingFixtureBag $resolvedFixtures,
        ResolvingContext $context
    ): array
    {
        $fixtures = new FixtureBag();
        foreach ($extendedFixtureReferences as $reference) {
            $fixtureId = $reference->getId();
            $context = $context->with($fixtureId);

            if (false === $unresolvedFixtures->has($fixtureId)) {
                throw FixtureNotFoundException::create($fixtureId);
            }

            if ($resolvedFixtures->has($fixtureId)) {
                $fixtures = $fixtures->with(
                    $resolvedFixtures->get($fixtureId)
                );

                continue;
            }

            $resolvedFixtures = $this->resolve(
                $unresolvedFixtures->get($fixtureId),
                $unresolvedFixtures,
                $resolvedFixtures,
                $context
            );

            $fixtures = $fixtures->with(
                $resolvedFixtures->get($fixtureId)
            );
        }

        return [$fixtures, $resolvedFixtures];
    }
}
