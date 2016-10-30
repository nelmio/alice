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

/**
 * A fixture is a value object representing an object to be built.
 */
interface FixtureIdInterface
{
    /**
     * @return string e.g. 'dummy0'. May contain flags e.g. 'dummy (extends base_dummy)' depending of the implementation.
     */
    public function getId(): string;
}
