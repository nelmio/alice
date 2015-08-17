<?php

namespace Nelmio\Alice\support\models;

class DynamicConstructorClass
{
    public $alpha;

    public function __construct()
    {
        $arguments = func_get_args();
        $this->alpha = $arguments[0];
    }
}
