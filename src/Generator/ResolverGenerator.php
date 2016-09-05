<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectSet;

final class ResolverGenerator implements GeneratorInterface
{
    use NotClonableTrait;

    /**
     * @var FixtureSetResolverInterface
     */
    private $resolver;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    public function __construct(FixtureSetResolverInterface $resolver, GeneratorInterface $generator)
    {
        $this->resolver = $resolver;
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function generate(FixtureSet $fixtureSet): ObjectSet
    {
        $resolvedFixtureSet = $this->resolver->resolve($fixtureSet);

        return $this->generator->generate($resolvedFixtureSet);
    }
}
