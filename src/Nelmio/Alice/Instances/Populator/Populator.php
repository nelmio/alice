<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator;

use InvalidArgumentException;
use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\PropertyDefinition;
use Nelmio\Alice\Instances\Populator\Methods\MethodInterface;
use Nelmio\Alice\Instances\Processor\Processor;

class Populator
{
    /**
    * @var Collection
    */
    protected $objects;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var Methods\MethodInterface[]
     */
    protected $setters;

    /**
     * @var array
     */
    private $uniqueValues = [];

    /**
     * @param Collection        $objects
     * @param Processor         $processor
     * @param MethodInterface[] $setters
     */
    public function __construct(Collection $objects, Processor $processor, array $setters)
    {
        foreach ($setters as $setter) {
            if (!($setter instanceof MethodInterface)) {
                throw new InvalidArgumentException("All setters passed into Populator must implement MethodInterface.");
            }
        }

        $this->objects   = $objects;
        $this->processor = $processor;
        $this->setters   = $setters;
    }

    /**
     * adds a populator for population extensions
     *
     * @param MethodInterface $setter
     **/
    public function addPopulator(MethodInterface $setter)
    {
        array_unshift($this->setters, $setter);
    }

    /**
     * populate all the properties for the object described by the given fixture
     *
     * @param Fixture $fixture
     */
    public function populate(Fixture $fixture)
    {
        $class  = $fixture->getClass();
        $name   = $fixture->getName();
        $object = $this->objects->get($name);

        foreach ($fixture->getProperties() as $property) {
            $key = $property->getName();
            $val = $property->getValue();

            if (is_array($val) && '{' === key($val)) {
                throw new \RuntimeException('Misformatted string in object '.$name.', '.$key.'\'s value should be quoted if you used yaml');
            }

            $value = $property->requiresUnique() ?
                $this->generateUnique($fixture, $property) :
                $this->processor->process($property, $fixture->getSetProperties(), $fixture->getValueForCurrent())
            ;

            foreach ($this->setters as $setter) {
                if ($setter->canSet($fixture, $object, $key, $value)) {
                    $setter->set($fixture, $object, $key, $value);
                    $fixture->setPropertyValue($key, $value);
                    break;
                }
            }

            if (!array_key_exists($key, $fixture->getSetProperties())) {
                throw new \UnexpectedValueException('Could not determine how to assign '.$key.' to a '.$class.' object');
            }
        }
    }

    /**
     * ensures that the property generated for the given fixture is a unique property
     *
     * @param  Fixture            $fixture
     * @param  PropertyDefinition $property
     * @return mixed
     */
    protected function generateUnique(Fixture $fixture, PropertyDefinition $property)
    {
        $class = $fixture->getClass();
        $key = $property->getName();
        $i = $uniqueTriesLimit = 128;

        do {
            // process values
            $value = $this->processor->process($property, $fixture->getSetProperties(), $fixture->getValueForCurrent());

            if (is_object($value)) {
                $valHash = spl_object_hash($value);
            } elseif (is_array($value)) {
                $valHash = hash('md4', serialize($value));
                @trigger_error(
                    'Uniqueness of an array will translate in unicity of the items instead of the array hash in Alice 3.0.0.',
                    E_USER_DEPRECATED
                );
            } else {
                $valHash = $value;
            }
        } while (--$i > 0 && isset($this->uniqueValues[$class . $key][$valHash]));

        if (isset($this->uniqueValues[$class . $key][$valHash])) {
            throw new \RuntimeException("Couldn't generate random unique value for $class: $key in $uniqueTriesLimit tries.");
        }

        $this->uniqueValues[$class . $key][$valHash] = true;

        return $value;
    }
}
