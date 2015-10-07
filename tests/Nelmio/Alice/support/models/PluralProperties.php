<?php

namespace Nelmio\Alice\support\models;

class PluralProperties extends PluralPropertiesParent
{
    private $properties;

    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty($property)
    {
        $this->properties[] = $property;
    }
}
