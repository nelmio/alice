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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectSet;

final class DoublePassGenerator implements GeneratorInterface
{
    use IsAServiceTrait;

    /**
     * @var FixtureSetResolverInterface
     */
    private $resolver;

    /**
     * @var ObjectGeneratorInterface
     */
    private $generator;

    public function __construct(FixtureSetResolverInterface $resolver, ObjectGeneratorInterface $generator)
    {
        $this->resolver = $resolver;
        $this->generator = $generator;
    }
    
    public function generate(FixtureSet $fixtureSet): ObjectSet
    {
        $resolvedFixtureSet = $this->resolver->resolve($fixtureSet);

        $context = new GenerationContext();
        $resolvedFixtureSet = $this->generateFixtures($resolvedFixtureSet, $context);
        $context->setToSecondPass();
        $resolvedFixtureSet = $this->generateFixtures($resolvedFixtureSet, $context);

        return new ObjectSet($resolvedFixtureSet->getParameters(), $resolvedFixtureSet->getObjects());
    }

    private function generateFixtures(ResolvedFixtureSet $set, GenerationContext $context): ResolvedFixtureSet
    {
        $fixtures = $set->getFixtures();

        foreach ($fixtures as $fixture) {
            $objects = $this->generator->generate($fixture, $set, $context);

            $set = $set->withObjects($objects);
        }

        return $set;
    }
}
