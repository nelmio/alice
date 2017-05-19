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

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

interface DenormalizerInterface
{
    /**
     * Denormalizes the parsed data into a comprehensive collection of fixtures.
     *
     * @param array $data PHP data coming from the parser
     *
     * @throws DenormalizationThrowable
     *
     * @return BareFixtureSet Contains the loaded parameters and fixtures.
     */
    public function denormalize(array $data): BareFixtureSet;
}
