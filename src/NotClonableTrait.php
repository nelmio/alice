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

trait NotClonableTrait
{
    public function __clone()
    {
        //TODO: throw a custom exception
        throw new \DomainException(
            'This class is not clonable. This could be the case because this has not been needed yet. Do not hesitate '
            .'to reach out the maintainers to know if this can be made clonable.'
        );
    }
}
