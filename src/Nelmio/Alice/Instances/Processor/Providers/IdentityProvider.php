<?php

namespace Nelmio\Alice\Instances\Processor\Providers;

class IdentityProvider
{
    public static function identity($val)
    {
        return $val;
    }
}
