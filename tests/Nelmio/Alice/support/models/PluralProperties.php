<?php

namespace Nelmio\Alice\support\models;

class PluralProperties
{
    private $fields;

    private $properties;

    public function getFields()
    {
        return $this->fields;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function addField($field)
    {
        $this->fields[] = $field;
    }

    public function addProperty($property)
    {
        $this->properties[] = $property;
    }
}
