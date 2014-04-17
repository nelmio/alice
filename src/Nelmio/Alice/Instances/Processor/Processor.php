<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor;

use InvalidArgumentException;
use Nelmio\Alice\Instances\Processor\Methods\MethodInterface;

class Processor
{
    /**
     * @var MethodInterface[]
     */
    private $methods;

    /**
     * @var string
     */
    private $valueForCurrent;

    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            if (!($method instanceof MethodInterface)) {
                throw new InvalidArgumentException("All methods passed into Processor must implement MethodInterface.");
            }
        }

        $this->methods = $methods;

        foreach ($this->methods as $method) {
            if (method_exists($method, 'setProcessor')) {
                $method->setProcessor($this);
            }
        }
    }

    /**
     * adds a processor for processing extensions
     *
     * @param MethodInterface $processor
     **/
    public function addProcessor(MethodInterface $processor)
    {
        array_unshift($this->methods, $processor);
    }

    /**
     * processes a given value to return a value that can be set on the actual instance
     *
     * @param  mixed  $valueOrProcessable - the original value (or value container) to be converted
     * @param  array  $variables
     * @param  string $valueForCurrent    - in the event a fixture will need to support <current()>, this value must be passed in at the top of the process loop
     * @return mixed
     */
    public function process($valueOrProcessable, array $variables, $valueForCurrent = null)
    {
        $value = $valueOrProcessable instanceof ProcessableInterface ? $valueOrProcessable->getValue() : $valueOrProcessable;

        if (!is_null($valueForCurrent)) { $this->valueForCurrent = $valueForCurrent; }

        foreach ($this->methods as $method) {
            $processable = new Processable($value);
            if ($method->canProcess($processable)) {
                if (method_exists($method, 'setValueForCurrent')) { $method->setValueForCurrent($this->valueForCurrent); }
                $value = $method->process($processable, $variables);
            }
        }

        if (!is_null($valueForCurrent)) { $this->valueForCurrent = null; }

        return $value;
    }

}
