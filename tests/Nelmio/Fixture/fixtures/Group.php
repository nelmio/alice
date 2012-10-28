<?php

namespace Nelmio\Fixture\fixtures;

class Group
{
    private $name;
    private $members = array();

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function addMember($member)
    {
        $this->members[] = $member;
    }
}