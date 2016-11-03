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

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

class UnsupportedScenarioException extends \UnexpectedValueException implements DenormalizationThrowable
{
    /**
     * @return static
     */
    public static function createForAmbiguousConstructor(
        FixtureInterface $fixture,
        string $parameterOrFunction,
        int $code = 0,
        \Throwable $previous = null
    ) {
        return new static(
            sprintf(
                'Could not denormalize the constructor of the fixture "%s" (%s) as it has both a constructor named'
                .' parameter and factory method "%s" and cannot determine which one to use.',
                $fixture->getId(),
                $fixture->getClassName(),
                $parameterOrFunction
            )
        );
    }
}
