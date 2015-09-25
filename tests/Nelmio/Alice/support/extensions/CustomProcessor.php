<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * this custom processor uppercases matching values.
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        return strtoupper($processable->getMatch('uppercaseMe'));
    }
}
