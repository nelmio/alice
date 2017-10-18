<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class Conditional implements MethodInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * sets the processor to handle recursive calls
     *
     * @param Processor $processor
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * {@inheritDoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        return is_string($processable->getValue()) && $processable->valueMatches('{^(?<threshold>[0-9.]+%?)\? (?<trueValue>.+?)(?: : (?<falseValue>.+?))?$}');
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $trueValue = $this->processor->process($processable->getMatch('trueValue'), $variables);

        if ($this->shouldReturnTrue($processable)) {
            return $trueValue;
        }
        
        if (!is_null($processable->getMatch('falseValue')) && '' !== $processable->getMatch('falseValue')) {
            return $this->processor->process($processable->getMatch('falseValue'), $variables);
        }
        
        return is_array($trueValue) ? [] : null;
    }

    /**
     * compares the threshold to a randomly generated value to determine whether
     *
     * @param  ProcessableInterface $processable
     * @return bool
     */
    private function shouldReturnTrue(ProcessableInterface $processable)
    {
        $threshold = $processable->getMatch('threshold');
        if ((float) $threshold != (int) $threshold) {
            @trigger_error(
                'Using floats for optional expressions such as "80%? true : false" is deprecated since 2.3.0 and will '
                .'throw an exception in Alice 3.0. Only integer values should be used.',
                E_USER_DEPRECATED
            );
        }

        if (0 == $threshold || 100 == $threshold) {
            @trigger_error(
                'The threshold value in optional expressions such as "80%? true : false" should be an interger element'
                .' of ]0;100[, i.e. the values 0 and 100 should not be used. This is deprecated since 2.3.0 and will'
                .'throw an exception in Alice 3.0. Only integer values should be used.',
                E_USER_DEPRECATED
            );
        }

        if (substr($threshold, -1) === '%') {
            $threshold = substr($threshold, 0, -1) / 100;
        }

        return $threshold > 0 && (mt_rand(0, 100) / 100) <= $threshold;
    }
}
