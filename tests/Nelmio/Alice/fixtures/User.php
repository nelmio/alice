<?php

namespace Nelmio\Alice\fixtures;

class User
{
    public $username;
    public $fullname;
    public $birthDate;
    public $email;
    public $favoriteNumber;

    public function __construct($username = null, $email = null)
    {
        $this->username = $username;
        $this->email    = $email;
    }

    public function getAge()
    {
        return 25;
    }
}
