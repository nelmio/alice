<?php

namespace Nelmio\Alice\fixtures;

class Topic
{
    public $id;
    public $subject;
    public $parentTopicId;
    public $parentCategory;

    public function __construct($id = null, $subject = null, $parentTopicId = null, $parentCategory = null)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->parentTopicId = $parentTopicId;
        $this->parentCategory = $parentCategory;
    }
}
