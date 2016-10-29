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

namespace Nelmio\Alice\Definition;

/**
 * Values encapsulate a row value to add a behaviour to it. For example, to provide the required elements for
 * generating unique values.
 */
interface ValueInterface
{
    /**
     * @return mixed
     */
    public function getValue();

    public function __toString(): string;
}
