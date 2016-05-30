<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture\Resolver;

use Nelmio\Alice\Builder\Fixture\FlaggedFixturesResolverInterface;
use Nelmio\Alice\UnresolvedFixtureBag;

final class DefaultFixturesResolver implements FlaggedFixturesResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(UnresolvedFixtureBag $fixtures): UnresolvedFixtureBag
    {
        $resolver = new TemplatesFixtureResolver();
        
        foreach ($fixtures as $fixture) {
            $resolvedFixtures = $resolver->resolve($fixture, $fixtures);
            $fixtures = $fixtures->mergeWith($resolvedFixtures);
        }
        
        return $fixtures;
    }
}
