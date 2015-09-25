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
        $this->magicProperties[$property] = $value.' set by magic setter';
    }
}
