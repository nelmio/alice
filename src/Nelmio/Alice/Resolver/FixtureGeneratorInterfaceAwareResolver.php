<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver;

use Nelmio\Alice\Generator\FixtureGeneratorInterface;

interface FixtureGeneratorInterfaceAwareResolver
{
    /**
     * @param FixtureGeneratorInterface $generator
     *
     * @return self
     */
    public function withGenerator(FixtureGeneratorInterface $generator);
}
