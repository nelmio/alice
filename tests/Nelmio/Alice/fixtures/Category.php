<?php

namespace Nelmio\Alice\fixtures;

class Category
{
    public $description;
    public $lastTopic;

    public function __construct($description = null, $lastTopic = null)
    {
        $this->description = $description;
        $this->lastTopic = $lastTopic;
    }
}
