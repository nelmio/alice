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


use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectSet;

final class SimpleDataLoader implements DataLoaderInterface
{
    use NotClonableTrait;

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
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        $fixtureSet = $this->builder->build($data, $parameters, $objects);

        return $this->generator->generate($fixtureSet);
    }
}
