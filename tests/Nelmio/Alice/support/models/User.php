<?php

namespace Nelmio\Alice\support\models;

class User
{
    public $uuid;
    public $username;
    public $fullname;
    public $display_name;
    public $birthDate;
    public $email;
    public $favoriteNumber;
    public $friends;
    public $family_name;

    public function __construct($username = null, $email = null, \DateTime $birthDate = null)
    {
        $this->setUsername($username ?: 'tmp-username');
        $this->email = $email;
        $this->birthDate = $birthDate;
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

    public function setFamilyName($family_name)
    {
        $this->family_name = $family_name;
    }

    public function setFavoriteNumber($favoriteNumber)
    {
        $this->favoriteNumber = $favoriteNumber;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setdisplay_name($display_name)
    {
        $this->display_name = $display_name;
    }

    public function setDisplayName($displayName)
    {
        $this->display_name = 'Mad ' . $displayName;
    }
}
