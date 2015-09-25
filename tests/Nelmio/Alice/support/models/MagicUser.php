<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\support\models;

class MagicUser
{
    public function __call($method, $args)
    {
        if (0 === strpos($method, 'set')) {
            $property = lcfirst(substr($method, 3));
            $this->$property = $args[0].' set by __call';

            return;
        }

        if (0 === strpos($method, 'get')) {
            $property = lcfirst(substr($method, 3));

            return $this->$property;
        }
    }
}
