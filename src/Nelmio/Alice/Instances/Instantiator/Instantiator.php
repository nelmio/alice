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

use InvalidArgumentException;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface;

class Instantiator
{
    /**
     * @var array
     **/
    protected $methods;

    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            if (!($method instanceof MethodInterface)) {
                throw new InvalidArgumentException("All methods passed into Instantiator must implement MethodInterface.");
            }
        }

        $this->methods   = $methods;
    }

    /**
     * adds an instantiator for instantiation extensions
     *
     * @param MethodInterface $instantiator
     **/
    public function addInstantiator(MethodInterface $instantiator)
    {
        array_unshift($this->methods, $instantiator);
    }

    /**
     * creates and returns an instance of the described class in the fixture
     *
     * @param  Fixture $fixture
     * @return mixed
     */
    public function instantiate(Fixture $fixture)
    {
        try {
            foreach ($this->methods as $method) {
                if ($method->canInstantiate($fixture)) {
                    return $method->instantiate($fixture);
                }
            }

      // exception otherwise
            throw new \RuntimeException("You must specify a __construct method with its arguments in object '{$fixture}' since class '{$fixture->getClass()}' has mandatory constructor arguments");
        } catch (\ReflectionException $exception) {
            $class = $fixture->getClass();

            return new $class();
        }
    }
}
