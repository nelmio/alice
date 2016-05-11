<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Chainable;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface;

final class ReflectionWithoutConstructorInstantiator implements ChainableInstantiatorInterface
{
    /**
     * @inheritdoc
     */
    public function instantiate(Fixture $fixture)
    {
        $reflectionClass = new \ReflectionClass($fixture->getClass());

        return $reflectionClass->newInstanceWithoutConstructor();
    }

    /**
     * @inheritdoc
     */
    public function canInstantiate(Fixture $fixture): bool
    {
        if (false === $fixture->shouldUseConstructor()) {
            return true;
        }
        
        try {
            $reflectionMethod = new \ReflectionMethod($fixture->getClass(), '__construct');

            return (
                false === $reflectionMethod->isPublic()
                && '__construct' === $fixture->getConstructorMethod()
            );
        } catch (\ReflectionException $exception) {
            // thrown when __construct does not exist, i.e. is default constructor
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
