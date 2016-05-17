<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Resolver;

use Nelmio\Alice\Throwable\ResolveThrowable;

class ResolverNotFoundException extends \RuntimeException implements ResolveThrowable
{
    /**
     * @param string $key Paramater key
     *
     * @return self
     */
    public static function create(string $key)
    {
        return new static(
            sprintf(
                'Not suitable resolver found for the parameter "%s".',
                $key
            )
        );
    }
}
