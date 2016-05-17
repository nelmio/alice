<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Throwable\BuildThrowable;

interface BuilderInterface
{
    /**
     * Builds the data retrieved by the parsed files or directly submitted data into a comprehensive list of parameters
     * and fixtures.
     *
     * @param array $data Parsed fixture files data.
     *
     * @throws BuildThrowable
     *
     * @return FixtureSet
     */
    public function build(array $data): FixtureSet;
}
