<?php

namespace Nelmio\Alice\fixtures;

class User
{
    public $username;
    public $fullname;
    public $birthDate;
    public $email;
    public $favoriteNumber;

    public function getAge()
    {
        return 25;
    }
}
