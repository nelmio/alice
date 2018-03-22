<?php

namespace Nelmio\Alice;

/**
 * @package Nelmio\Alice
 */
class UserDetail
{
    private $email;

    /**
     * @var User
     */
    private $user;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return UserDetail
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return UserDetail
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }
}