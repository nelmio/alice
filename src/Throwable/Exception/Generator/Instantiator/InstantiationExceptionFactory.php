<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Throwable\Exception\Generator\Instantiator;

use Nelmio\Alice\FixtureInterface;
use Throwable;

/**
 * @private
 */
final class InstantiationExceptionFactory
{
    public static function create(FixtureInterface $fixture, int $code, Throwable $previous): InstantiationException
    {
        return new InstantiationException(
            sprintf(
                'Could not instantiate fixture "%s".',
                $fixture->getId()
            ),
            $code,
            $previous
        );
    }

    public static function createForNonPublicConstructor(FixtureInterface $fixture): InstantiationException
    {
        return new InstantiationException(
            sprintf(
                'Could not instantiate "%s", the constructor of "%s" is not public.',
                $fixture->getId(),
                $fixture->getClassName()
            )
        );
    }

    public static function createForConstructorIsMissingMandatoryParameters(FixtureInterface $fixture): InstantiationException
    {
        return new InstantiationException(
            sprintf(
                'Could not instantiate "%s", the constructor has mandatory parameters but no parameters have been given.',
                $fixture->getId()
            )
        );
    }

    public static function createForCouldNotGetConstructorData(
        FixtureInterface $fixture,
        int $code = 0,
        Throwable $previous = null
    ): InstantiationException {
        return new InstantiationException(
            sprintf(
                'Could not get the necessary data on the constructor to instantiate "%s".',
                $fixture->getId()
            ),
            $code,
            $previous
        );
    }

    public static function createForInvalidInstanceType(FixtureInterface $fixture, ?object $instance): InstantiationException
    {
        return new InstantiationException(
            sprintf(
                'Instantiated fixture was expected to be an instance of "%s". Got "%s" instead.',
                $fixture->getClassName(),
                $instance ? get_class($instance) : 'null'
            )
        );
    }

    public static function createForInstantiatorNotFoundForFixture(FixtureInterface $fixture): InstantiatorNotFoundException
    {
        return new InstantiatorNotFoundException(
            sprintf(
                'No suitable instantiator found for the fixture "%s".',
                $fixture->getId()
            )
        );
    }

    private function __construct()
    {
    }
}
