<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $this->username = $username ?: 'tmp-username';
        $this->email = $email;
        $this->birthDate = $birthDate;
    }

    public static function create($username = null, $email = null, \DateTime $birthDate = null)
    {
        return new static($username.'-from-create', $email, $birthDate);
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
        $this->$key = $value.' set by custom setter';
    }
}
