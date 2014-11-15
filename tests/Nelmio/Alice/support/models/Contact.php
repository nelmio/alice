<?php

namespace Nelmio\Alice\support\models;

class Contact
{
    private $user;

    protected $magicProperties = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function __get($property)
    {
        return $this->magicProperties[$property];
    }

    public function __set($property, $value)
    {
        $this->magicProperties[$property] = $value . ' set by magic setter';
    }
}
