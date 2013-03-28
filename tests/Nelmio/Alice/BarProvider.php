<?php
namespace Nelmio\Alice;

class BarProvider extends \Faker\Provider\Base
{
    public static function bar($str)
    {
        return 'bar' . $str;
    }
}
