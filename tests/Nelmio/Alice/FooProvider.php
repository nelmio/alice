<?php
namespace Nelmio\Alice;

class FooProvider
{
    public static function foo($str)
    {
        return 'foo' . $str;
    }
}
