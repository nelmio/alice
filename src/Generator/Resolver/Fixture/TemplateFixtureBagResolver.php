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
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Resolver\FixtureBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\NotClonableTrait;

/**
 * Decorates a simple fixture resolver to resolve templates fixtures.
 */
final class TemplateFixtureBagResolver implements FixtureBagResolverInterface
{
    use NotClonableTrait;

    /**
     * @var TemplateFixtureResolver
     */
    private $resolver;

    public function __construct()
    {
        $this->resolver = new TemplateFixtureResolver();
    }

    /**
     * @inheritdoc
     */
    public function resolve(FixtureBag $unresolvedFixtures): FixtureBag
    {
        $resolvedFixtures = new TemplatingFixtureBag();
        foreach ($unresolvedFixtures as $fixture) {
            /** @var FixtureInterface $fixture */
            $id = $fixture->getId();

            if ($resolvedFixtures->has($id)) {
                continue;
            }

            if (false === $fixture instanceof TemplatingFixture) {
                $resolvedFixtures = $resolvedFixtures->with($fixture);

                continue;
            }

            $context = new ResolvingContext($id);
            $resolvedFixtures = $this->resolver->resolve(
                $fixture,
                $unresolvedFixtures,
                $resolvedFixtures,
                $context
            );
        }

        return $resolvedFixtures->getFixtures();
    }
}
