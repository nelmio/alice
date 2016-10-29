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

trait NotCallableTrait
{
    public function __call($method, $arguments)
    {
        throw new \DomainException(
            sprintf(
                'Did not expect "%s" to be called.',
                $method
            )
        );
    }
}
