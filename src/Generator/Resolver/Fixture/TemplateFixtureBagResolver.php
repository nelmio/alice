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

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Resolver\FixtureBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\IsAServiceTrait;

/**
 * Decorates a simple fixture resolver to resolve templates fixtures.
 */
final class TemplateFixtureBagResolver implements FixtureBagResolverInterface
{
    use IsAServiceTrait;

    /**
     * @var TemplateFixtureResolver
     */
    private $resolver;

    public function __construct()
    {
        $this->resolver = new TemplateFixtureResolver();
    }
    
    public function resolve(FixtureBag $unresolvedFixtures): FixtureBag
    {
        $resolvedFixtures = new TemplatingFixtureBag();
        foreach ($unresolvedFixtures as $fixture) {
            $resolvedFixtures = $this->resolveFixture(
                $this->resolver,
                $fixture,
                $unresolvedFixtures,
                $resolvedFixtures
            );
        }

        return $resolvedFixtures->getFixtures();
    }

    public function resolveFixture(
        TemplateFixtureResolver $resolver,
        FixtureInterface $fixture,
        FixtureBag $unresolvedFixtures,
        TemplatingFixtureBag $resolvedFixtures
    ): TemplatingFixtureBag {
        /** @var FixtureInterface $fixture */
        $id = $fixture->getId();

        if ($resolvedFixtures->has($id)) {
            return $resolvedFixtures;
        }

        if (false === $fixture instanceof TemplatingFixture) {
            return $resolvedFixtures->with($fixture);
        }

        $context = new ResolvingContext($id);

        return $resolver->resolve(
            $fixture,
            $unresolvedFixtures,
            $resolvedFixtures,
            $context
        );
    }
}
