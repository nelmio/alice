<?php

namespace Nelmio\Alice\support\models;

class PluralPropertiesParent
{
    private $fields;

    public function getFields()
    {
        return $this->fields;
    }

    public function addField($field)
    {
        $this->fields[] = $field;
    }
}
