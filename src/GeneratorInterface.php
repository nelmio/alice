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

namespace Nelmio\Alice;

use Nelmio\Alice\Throwable\GenerationThrowable;

interface GeneratorInterface
{
    /**
     * Generates a list of parameters and objects from the given set of data.
     *
     *
     * @throws GenerationThrowable
     *
     * @return ObjectSet Contains the parameters and objects built from the loaded and injected ones.
     */
    public function generate(FixtureSet $fixtureSet): ObjectSet;
}
