<?php

namespace Nelmio\Alice\support\models;

class User
{
    public $uuid;
    public $username;
    public $fullname;
    public $birthDate;
    public $email;
    public $favoriteNumber;
    public $friends;

    public function __construct($username = null, $email = null, \DateTime $birthDate = null)
    {
        $this->username = $username;
        $this->email = $email;
        $this->birthDate = $birthDate;
    }

    public static function create($username = null, $email = null, \DateTime $birthDate = null)
    {
        return new static($username . '-from-create', $email, $birthDate);
    }

    public static function bogusCreate($username = null, $email = null, \DateTime $birthDate = null)
    {
    }

    public function getAge()
    {
        return 25;
    }

    public function doStuff($dummy, $dummy2, $arg)
    {
        $this->username = $arg;
    }

    public function customSetter($key, $value)
    {
        $this->$key = $value . ' set by custom setter';
    }
}
