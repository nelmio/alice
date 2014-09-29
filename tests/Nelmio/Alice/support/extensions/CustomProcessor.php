<?php

namespace Nelmio\Alice\support\extensions;

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Processor\Methods\MethodInterface as ProcessorInterface;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;
use Nelmio\Alice\Instances\Processor\Processor;

class CustomProcessor implements ProcessorInterface
{
    public function setObjects(Collection $objects)
    {
        $this->objects = $objects;
    }

    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function canProcess(ProcessableInterface $processable)
    {
        return is_string($processable->getValue()) && $processable->valueMatches('/^uppercase processor:(?<uppercaseMe>[a-z\s]+?)$/');
    }

    /**
     * this custom processor uppercases matching values
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        return strtoupper($processable->getMatch('uppercaseMe'));
    }
}
