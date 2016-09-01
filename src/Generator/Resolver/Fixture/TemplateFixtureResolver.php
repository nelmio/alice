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
use Nelmio\Alice\NotClonableTrait;

final class TemplateFixtureResolver
{
    use NotClonableTrait;

    /**
     * Resolves a given fixture. The resolution of a fixture may result in the resolution of several fixtures.
     *
     * @param TemplatingFixture|FixtureInterface $fixture Fixture to resolve
     * @param FixtureBag                         $unresolvedFixtures
     * @param TemplatingFixtureBag               $resolvedFixtures
     * @param ResolvingContext                   $context
     *
     * @throws FixtureNotFoundException
     *
     * @return TemplatingFixtureBag
     */
    public function resolve(
        TemplatingFixture $fixture,
        FixtureBag $unresolvedFixtures,
        TemplatingFixtureBag $resolvedFixtures,
        ResolvingContext $context
    ): TemplatingFixtureBag
    {
        $context->checkForCircularReference($fixture->getId());

        if (false === $fixture->extendsFixtures()) {
            return $resolvedFixtures->with($fixture);
        }

        /**
         * @var FixtureBag           $extendedFixtures
         * @var TemplatingFixtureBag $resolvedFixtures
         */
        list($extendedFixtures, $resolvedFixtures) = $this->resolveExtendedFixtures(
            $fixture,
            $fixture->getExtendedFixturesReferences(),
            $unresolvedFixtures,
            $resolvedFixtures,
            $context
        );
        $fixture = $this->getExtendedFixture($fixture, $extendedFixtures);

        return $resolvedFixtures->with($fixture);
    }

    /**
     * @param FixtureReference[]   $extendedFixtureReferences
     * @param FixtureBag           $unresolvedFixtures
     * @param TemplatingFixtureBag $resolvedFixtures
     * @param ResolvingContext     $context
     *
     * @throws FixtureNotFoundException
     *
     * @return array The first element is a FixtureBag with all the extended fixtures and the second is a
     *               TemplatingFixtureBag which may contain new fixtures (from the resolution)
     */
    private function resolveExtendedFixtures(
        TemplatingFixture $fixture,
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
                if (false === $resolvedFixtures->hasTemplate($fixtureId)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Fixture "%s" extends "%s" but "%s" is not a template.',
                            $fixture->getId(),
                            $fixtureId,
                            $fixtureId
                        )
                    );
                }

                $fixtures = $fixtures->with(
                    $resolvedFixtures->getTemplate($fixtureId)
                );

                continue;
            }

            $unresolvedFixture = $unresolvedFixtures->get($fixtureId);
            if (false === $unresolvedFixture instanceof TemplatingFixture) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Fixture "%s" extends "%s" but "%s" is not a template.',
                        $fixture->getId(),
                        $fixtureId,
                        $fixtureId
                    )
                );
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

    public function getExtendedFixture(TemplatingFixture $fixture, FixtureBag $extendedFixtures)
    {
        $specs = $fixture->getSpecs();
        foreach ($extendedFixtures as $extendedFixture) {
            /** @var FixtureInterface $extendedFixture */
            $specs = $specs->mergeWith($extendedFixture->getSpecs());
        }

        return $fixture
            ->withSpecs($specs)
        ;
    }
}
