<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

class EmptyConstructor implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canInstantiate(Fixture $fixture)
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
            // thrown when __construct does not exist, i.e. is default constructor
            return 1 === preg_match('/(?:Method )(.+)(?: does not exist)/', $exception->getMessage());
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        $class = $fixture->getClass();

        return new $class();
    }
}
