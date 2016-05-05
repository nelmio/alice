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

class ReflectionWithoutConstructor implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canInstantiate(Fixture $fixture)
    {
        if (!$fixture->shouldUseConstructor()) {
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

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        $reflClass = new \ReflectionClass($fixture->getClass());

        return $reflClass->newInstanceWithoutConstructor();
    }
}
