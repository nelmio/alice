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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Throwable\ResolutionThrowable;

interface FixtureBagResolverInterface
{
    /**
     * Resolves a collection of fixtures.
     *
     *
     * @throws ResolutionThrowable
     *
     * @return FixtureBag Resolved fixtures.
     */
    public function resolve(FixtureBag $unresolvedFixtures): FixtureBag;
}
