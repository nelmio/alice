<?php

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

	public function addInstance($name, $instance)
	{
		return $this->instances[$name] = $instance;
	}

}