<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface;

/**
 * The instantiator is responsible for creating an object defined by its fixture.
 */
class Instantiator
{
    /**
     * @var MethodInterface[]
     **/
    protected $methods;

    /**
     * @param MethodInterface[] $methods
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            if (!($method instanceof MethodInterface)) {
                throw new \InvalidArgumentException(
                    'All methods passed into Instantiator must implement MethodInterface.'
                );
            }
        }

        $this->methods = $methods;
    }

    /**
     * Adds an instantiator for instantiation extensions.
     *
     * @param MethodInterface $instantiator
     **/
    public function addInstantiator(MethodInterface $instantiator)
    {
        array_unshift($this->methods, $instantiator);
    }

    /**
     * Creates and returns an instance of the described class in the fixture.
     *
     * @param Fixture $fixture
     *
     * @throws \RuntimeException
     *
     * @return object Object described by the fixture (not populated yet)
     */
    public function instantiate(Fixture $fixture)
    {
        try {
            foreach ($this->methods as $method) {
                if ($method->canInstantiate($fixture)) {
                    return $method->instantiate($fixture);
                }
            }

            throw new \RuntimeException(
                "You must specify a __construct method with its arguments in object '{$fixture}' since class "
                ."'{$fixture->getClass()}' has mandatory constructor arguments"
            );
        } catch (\ReflectionException $exception) {
            $class = $fixture->getClass();

            return new $class();
        }
    }
}
