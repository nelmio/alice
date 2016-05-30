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

use Nelmio\Alice\BuilderInterface;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\LoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParserInterface;

final class DefaultLoader implements LoaderInterface
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    public function __construct(ParserInterface $parser, BuilderInterface $builder, GeneratorInterface $generator)
    {
        $this->parser = $parser;
        $this->builder = $builder;
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function load(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $data = $this->parser->parse($file);
        $unresolvedFixturesSet = $this->builder->build($data);

        return $this->generator->generate(
            $unresolvedFixturesSet,
            new ParameterBag($parameters),
            new ObjectBag($objects)
        );
    }
}
