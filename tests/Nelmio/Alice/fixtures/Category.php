<?php

namespace Nelmio\Alice\fixtures;

class Category
{
    public $id;
    public $description;
    public $lastTopic;

    public function __construct($id = null, $description = null, $lastTopic = null)
    {
        $this->id = $id;
        $this->description = $description;
        $this->lastTopic = $lastTopic;
    }
}
