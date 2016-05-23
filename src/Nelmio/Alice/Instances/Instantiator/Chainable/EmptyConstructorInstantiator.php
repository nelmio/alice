<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Chainable;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface;

final class EmptyConstructorInstantiator implements ChainableInstantiatorInterface
{
    /**
     * @inheritdoc
     */
    public function instantiate(Fixture $fixture)
    {
        $class = $fixture->getClass();

        return new $class();
    }

    /**
     * @inheritdoc
     */
    public function canInstantiate(Fixture $fixture): bool
    {
        try {
            $reflectionMethod = new \ReflectionMethod($fixture->getClass(), '__construct');

            return 0 === $reflectionMethod->getNumberOfRequiredParameters();
        } catch (\ReflectionException $exception) {
            // thrown when __construct does not exist, i.e. is default constructor
            return true;
        }
    }
}
