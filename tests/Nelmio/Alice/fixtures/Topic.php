<?php

namespace Nelmio\Alice\fixtures;

class Topic
{
    public $subject;
    public $parentCategory;

    public function __construct($subject = null, $parentCategory = null)
    {
        $this->subject = $subject;
        $this->parentCategory = $parentCategory;
    }
}
