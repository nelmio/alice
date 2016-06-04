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

use Nelmio\Alice\Fixture\UnresolvedFixtureWithFlags;
use Nelmio\Alice\Fixture\UnresolvedTemplatingFixture;
use Nelmio\Alice\UnresolvedFixtureBag;
use Nelmio\Alice\UnresolvedFixtureInterface;

final class TemplatesFixtureResolver
{
    /**
     * @var UnresolvedFixtureBag
     */
    private $templates;

    public function __construct()
    {
        $this->templates = new UnresolvedFixtureBag();
    }

    public function resolve(
        UnresolvedFixtureInterface $unresolvedFixture,
        UnresolvedFixtureBag $fixtures,
        ResolvingContext $context = null): UnresolvedFixtureBag
    {
        if (false === $unresolvedFixture instanceof UnresolvedFixtureWithFlags) {
            return new UnresolvedFixtureBag();
        }
        /* @var UnresolvedFixtureWithFlags $unresolvedFixture */
        $fixture = new UnresolvedTemplatingFixture($unresolvedFixture);
        $context = ResolvingContext::createFrom($context, $unresolvedFixture->getReference());
        
        $context->checkForCircularReference($unresolvedFixture->getReference());
        list($fixture, $bag) = $this->resolveExtends(new UnresolvedFixtureBag(), $fixture, $fixtures, $context);
        /* @var UnresolvedTemplatingFixture $fixture */
        /* @var UnresolvedFixtureBag $bag */
        
        if ($fixture->isATemplate()) {
            $this->templates = $this->templates->with($fixture);
        } else {
            $bag = $bag->with($fixture);
        }
        
        return $bag;
    }

    private function resolveExtends(
        UnresolvedFixtureBag $bag,
        UnresolvedTemplatingFixture $fixture,
        UnresolvedFixtureBag $fixtures,
        ResolvingContext $context
    ): array
    {
        if (false === $fixture->extendsFixtures()) {
            return $bag;
        }

        $specs = $fixture->getSpecs();
        foreach ($fixture->getExtendedFixtures() as $extendedFixtureReference) {
            $bag = $bag->mergeWith(
                $this->resolve(
                    $fixtures->get($extendedFixtureReference),
                    $fixtures,
                    $context
                )
            );
            
            $extendedFixtureSpecs = $bag->get($extendedFixtureReference)->getSpecs();
            $specs = $extendedFixtureSpecs->mergeWith($specs);
        }
        
        return [
            $fixture->withSpecs($specs),
            $bag
        ];
    }
}
