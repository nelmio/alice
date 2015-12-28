<?php

namespace Nelmio\Alice\support\models;

class StaticUser
{
    public $username;
    public $email;

    public function __construct($username, $email)
    {
        $this->username = $username;
        $this->email = $email;
    }

    public static function create($email)
    {
        return new static(strtok($email, '@'), $email);
    }

    public static function bogusCreate($username, $email)
    {
    }
}
