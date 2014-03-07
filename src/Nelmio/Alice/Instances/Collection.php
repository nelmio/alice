<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances;

use Doctrine\Common\Collections\ArrayCollection;

class Collection extends ArrayCollection {

	public function addAll($instances)
	{
		foreach ($instances as $instance) {
			$this->set($instance->name, $instance);
		}
	}

	public function toObjectArray()
	{
		$objectArray = array();

		$this->forAll(function($name, $instance) use (&$objectArray) {
			$objectArray[$name] = $instance->asObject();
		});

		return $objectArray;
	}

	public function getInstance($name, $property = null)
	{
		if ($this->containsKey($name)) {
			$object = $this->get($name)->asObject();

			if ($property !== null) {
				if (property_exists($object, $property)) {
					$prop = new \ReflectionProperty($object, $property);

					if ($prop->isPublic()) {
						return $object->{$property};
					}
				}

				$getter = 'get'.ucfirst($property);
				if (method_exists($object, $getter) && is_callable(array($object, $getter))) {
					return $object->$getter();
				}

				throw new \UnexpectedValueException('Property '.$property.' is not defined for instance '.$name);
			}

			return $object;
		}

		throw new \UnexpectedValueException('Instance '.$name.' is not defined');
	}

	public function getRandomInstances($mask, $count=1, $property)
	{
		if ($count === 0) {
        return array();
    }

    $availableInstances = array();
    foreach ($this->toArray() as $key => $val) {
        if (preg_match('{^'.str_replace('*', '.+', $mask).'$}', $key)) {
            $availableInstances[] = $key;
        }
    }

    if (!$availableInstances) {
        throw new \UnexpectedValueException('Instance mask "'.$mask.'" did not match any existing instance, make sure the object is created after its references');
    }

    if (null === $count) {
        return $this->getInstance($availableInstances[mt_rand(0, count($availableInstances) - 1)], $property);
    }

    $res = array();
    while ($count-- && $availableInstances) {
        $ref = array_splice($availableInstances, mt_rand(0, count($availableInstances) - 1), 1);
        $res[] = $this->getInstance(current($ref), $property);
    }

    return $res;
	}

}