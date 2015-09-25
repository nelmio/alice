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

class Group
{
    private $name;
    private $sortName;
    private $owner;
    private $members = [];
    private $creationDate;
    private $contactEmail;
    private $supportEmails = [];
    public $contactPerson;
    public $contactPersonName;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSortName()
    {
        return $this->sortName;
    }

    protected function setSortName($sortName)
    {
        $this->sortName = $sortName;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function addMember(User $member)
    {
        $this->members[] = $member;
    }

    public function setCreationDate(\DateTime $date)
    {
        $this->creationDate = $date;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setContactEmail($email)
    {
        $this->contactEmail = $email;
    }

    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    public function addSupportEmail($email)
    {
        $this->supportEmails[] = $email;
    }

    public function getSupportEmails()
    {
        return $this->supportEmails;
    }
}
