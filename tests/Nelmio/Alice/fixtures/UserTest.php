<?php

namespace Nelmio\Alice\fixtures;

class UserTest
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
