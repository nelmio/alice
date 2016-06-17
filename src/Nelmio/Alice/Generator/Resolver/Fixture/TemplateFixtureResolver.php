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
use Nelmio\Alice\Generator\Resolver\ParameterResolvingContext;

final class TemplateFixtureResolver
{
    /**
     * Resolves a given fixture. The resolution of a fixture may result in the resolution of several fixtures.
     *
     * @param FixtureInterface          $fixture Fixture to resolve
     * @param FixtureBag                $unresolvedFixtures
     * @param FixtureBag                $resolvedFixtures
     *
     * @param ParameterResolvingContext $context
     *
     * @return FixtureBag
     */
    public function resolve(
        TemplatingFixture $fixture,
        FixtureBag $unresolvedFixtures,
        TemplatingFixtureBag $resolvedFixtures,
        ParameterResolvingContext $context
    ): TemplatingFixtureBag
    {
        $context->checkForCircularReference($fixture->getId());

        if ($fixture->extendsFixtures()) {
            /**
             * @var FixtureBag           $extendedFixtures
             * @var TemplatingFixtureBag $resolvedFixtures
             */
            list($extendedFixtures, $resolvedFixtures) = $this->resolveExtendedFixtures(
                $fixture->getExtendedFixturesReferences(),
                $unresolvedFixtures,
                $resolvedFixtures,
                $context
            );

            $specs = $fixture->getSpecs();
            foreach ($extendedFixtures as $extendedFixture) {
                /** @var FixtureInterface $extendedFixture */
                $specs = $specs->mergeWith($extendedFixture->getSpecs());
            }

            $fixture = $fixture->withSpecs($specs);
        }
        $resolvedFixtures->with($fixture);

        return $resolvedFixtures;
    }

    /**
     * @param FixtureReference[]                     $fixturesReferences
     * @param FixtureBag                             $unresolvedFixtures
     * @param TemplatingFixture|TemplatingFixtureBag $resolvedFixtures
     * @param ParameterResolvingContext       $context
     *
     * @throws FixtureNotFoundException
     *
     * @return array<FixtureBag, TemplatingFixtureBag>
     */
    private function resolveExtendedFixtures(
        array $fixturesReferences,
        FixtureBag $unresolvedFixtures,
        TemplatingFixture $resolvedFixtures,
        ParameterResolvingContext $context
    ): array
    {
        $fixtures = new FixtureBag();
        foreach ($fixturesReferences as $reference) {
            $id = $reference->getId();
            $context = $context->with($id);

            if (false === $unresolvedFixtures->has($id)) {
                throw FixtureNotFoundException::create($id);
            }

            if ($resolvedFixtures->has($id)) {
                $fixtures = $fixtures->with(
                    $resolvedFixtures->get($id)
                );

                continue;
            }

            $resolvedFixtures = $this->resolve(
                $unresolvedFixtures->get($id),
                $unresolvedFixtures,
                $resolvedFixtures,
                $context
            );

            $fixtures = $fixtures->with(
                $resolvedFixtures->get($id)
            );
        }

        return [$fixtures, $resolvedFixtures];
    }
}
