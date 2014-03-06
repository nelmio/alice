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

class Collection {

	private $instances = array();

	public function getInstances()
	{
		return $this->instances;
	}

	public function setInstances(array $instances)
	{
		$this->instances = $instances;
	}

	public function addInstance($name, $instance)
	{
		return $this->instances[$name] = $instance;
	}

	public function getInstance($name, $property = null)
	{
		if (isset($this->instances[$name])) {
			$instance = $this->instances[$name];

			if ($property !== null) {
				if (property_exists($instance, $property)) {
					$prop = new \ReflectionProperty($instance, $property);

					if ($prop->isPublic()) {
						return $instance->{$property};
					}
				}

				$getter = 'get'.ucfirst($property);
				if (method_exists($instance, $getter) && is_callable(array($instance, $getter))) {
					return $instance->$getter();
				}

				throw new \UnexpectedValueException('Property '.$property.' is not defined for instance '.$name);
			}

			return $this->instances[$name];
		}

		throw new \UnexpectedValueException('Instance '.$name.' is not defined');
	}

	public function getRandomInstances($mask, $count=1, $property)
	{
		if ($count === 0) {
        return array();
    }

    $availableInstances = array();
    foreach ($this->instances as $key => $val) {
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