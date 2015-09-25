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
