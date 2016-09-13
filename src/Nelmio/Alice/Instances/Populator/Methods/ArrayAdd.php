<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Util\TypeHintChecker;

class ArrayAdd implements MethodInterface
{
    /**
     * @var TypeHintChecker
     */
    protected $typeHintChecker;

    /**
     * @var <string, string>|null Class method pair
     */
    private $singularizer;

    /**
     * @param TypeHintChecker $typeHintChecker
     */
    public function __construct(TypeHintChecker $typeHintChecker)
    {
        $this->typeHintChecker = $typeHintChecker;
        $this->detectSingularizeMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return is_array($value) && $this->findAdderMethod($object, $property) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $method = $this->findAdderMethod($object, $property);
        foreach ($value as $val) {
            $val = $this->typeHintChecker->check($object, $method, $val);
            $object->{$method}($val);
        }
    }

    /**
     * Finds the method used to append values to the named property.
     *
     * @param mixed  $object
     * @param string $property
     *
     * @return string|null Method name or null if adder method not detected
     */
    private function findAdderMethod($object, $property)
    {
        if (method_exists($object, $method = 'add'.$property)) {
            return $method;
        }

        foreach ($this->singularize($property) as $singularForm) {
            $adder = 'add'.$singularForm;
            if (method_exists($object, $adder)) {
                return $adder;
            }
        }

        $method = 'add'.rtrim($property, 's');
        if (method_exists($object, $method)) {
            return $method;
        }

        $method = 'add'.substr($property, 0, -3).'y';
        if (substr($property, -3) === 'ies' && method_exists($object, $method)) {
            return $method;
        }

        return null;
    }

    /**
     * @param string $plural
     *
     * @return string[]
     */
    private function singularize($plural)
    {
        if ($this->singularizer) {
            return (array) call_user_func($this->singularizer, $plural);
        }

        return [];
    }

    /**
     * Tries to detect a singularize method that is available.
     */
    private function detectSingularizeMethod()
    {
        $classes = [
            'Symfony\Component\Inflector\Inflector' => 'singularize',
            'Symfony\Component\PropertyAccess\StringUtil' => 'singularify',
            'Symfony\Component\Form\Util\FormUtil' => 'singularify',
            'Doctrine\Common\Inflector\Inflector' => 'singularize',
        ];

        foreach ($classes as $class => $method) {
            if (class_exists($class) && method_exists($class, $method)) {
                // cache the singularize method
                $this->singularizer = [$class, $method];

                return;
            }
        }
    }
}
