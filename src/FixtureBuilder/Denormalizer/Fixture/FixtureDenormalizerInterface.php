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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

interface FixtureDenormalizerInterface
{
    /**
     * A more specific version of {@see \Nelmio\Alice\BuilderInterface} dedicated to fixtures.
     *
     * @param FixtureBag $builtFixtures
     * @param string     $className FQCN (no flags)
     * @param string     $fixtureId
     * @param array      $specs     Contains the list of property calls, constructor specification and method calls
     * @param FlagBag    $flags     Flags inherited from the namespace.
     *
     * @throws DenormalizationThrowable
     *
     * @return FixtureBag $builtFixtures with the new built fixtures.
     */
    public function denormalize(FixtureBag $builtFixtures, string $className, string $fixtureId, array $specs, FlagBag $flags): FixtureBag;
}
