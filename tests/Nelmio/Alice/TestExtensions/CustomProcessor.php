<?php

namespace Nelmio\Alice\TestExtensions;

use Nelmio\Alice\Instances\Processor\Methods\MethodInterface as ProcessorInterface;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class CustomProcessor implements ProcessorInterface {

	public function canProcess(ProcessableInterface $processable)
	{
		return $processable->valueMatches('/^uppercase processor:(?<uppercaseMe>[a-z\s]+?)$/');
	}

	/**
	 * this custom processor uppercases matching values
	 */
	public function process(ProcessableInterface $processable, array $variables)
	{
		return strtoupper($processable->getMatch('uppercaseMe'));
	}

}