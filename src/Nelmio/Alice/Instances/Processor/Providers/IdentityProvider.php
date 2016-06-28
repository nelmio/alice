<?php

namespace Nelmio\Alice\Instances\Processor\Providers;

class IdentityProvider
{
    /**
     * Returns whatever is passed to it. This allows you among other things to use a PHP expression while still
     * benefiting from variable replacement.
     *
     * @param mixed $val
     *
     * @return mixed
     */
    public static function identity($val)
    {
        return $val;
    }
}
