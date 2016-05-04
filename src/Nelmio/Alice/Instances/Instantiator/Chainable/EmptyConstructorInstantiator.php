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
        $className = $fixture->getClass();

        return new $className();
    }

    /**
     * @inheritdoc
     */
    public function canInstantiate(Fixture $fixture): bool
    {
        try {
            if ('__construct' !== $fixture->getConstructorMethod()) {
                return false;
            }

            $reflectionMethod = new \ReflectionMethod($fixture->getClass(), '__construct');

            return (
                $reflectionMethod->isPublic()
                && 0 === $reflectionMethod->getNumberOfRequiredParameters()
                && 0 === count($fixture->getConstructorArgs())
            );
        } catch (\ReflectionException $exception) {
            if (1 === preg_match('/(?:Method )(.+)(?: does not exist)/', $exception->getMessage())) {
                // thrown when __construct does not exist, i.e. is default constructor
                return true;
            }

            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
