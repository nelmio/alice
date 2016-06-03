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

use Nelmio\Alice\Object;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\Fixture\ResolvingContext;
use Nelmio\Alice\UnresolvedFixtureBag;

final class DefaultFixtureBagGenerator implements FixtureBagGeneratorInterface
{
    /**
     * @var FixtureGeneratorInterface
     */
    private $generator;

    public function __construct(FixtureGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function generate(UnresolvedFixtureBag $fixtures, ParameterBag $parameters, ObjectBag $injectedObjects): ObjectBag
    {
        $objects = $injectedObjects;
        foreach ($fixtures as $key => $fixture) {
            if ($objects->has($key)) {
                continue;
            }

            $context = new ResolvingContext($key);
            $generatedObjects = $this->generator->generate(
                $fixture,
                $parameters,
                $fixtures,
                $objects,
                $context
            );

            foreach ($generatedObjects as $generatedObjectKey => $generatedObject) {
                $objects = $objects->with(new Object($generatedObjectKey, $generatedObject));
            }
        }

        return $objects;
    }
}
