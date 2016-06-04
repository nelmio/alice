<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;


use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\LoaderInterface;
use Nelmio\Alice\ObjectSet;

final class SimpleLoader implements LoaderInterface
{
    /**
     * @var FixtureBuilderInterface
     */
    private $builder;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    public function __construct(FixtureBuilderInterface $fixtureBuilder, GeneratorInterface $generator)
    {
        $this->builder = $fixtureBuilder;
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function load(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $fixtureSet = $this->builder->build($file, $parameters, $objects);

        return $this->generator->generate($fixtureSet);
    }
    
    public function __clone()
    {
        $this->builder = clone $this->builder;
        $this->generator = clone $this->generator;
    }
}
