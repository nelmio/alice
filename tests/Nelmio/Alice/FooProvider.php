<?php
namespace Nelmio\Alice;

class FooProvider extends \Faker\Provider\Base
{
    public static function foo($str)
    {
        return 'foo' . $str;
    }
}