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
 * Value object to point to refer to a service. For example, can be an instantiated service or a reference to a static
 * class.
 */
interface ServiceReferenceInterface
{
    public function getId(): string;
}
