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

use Nelmio\Alice\Fixtures\ParameterBag;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class Parameterized implements MethodInterface
{
    /**
     * @var ParameterBag
     */
    private $parameters;

    public function __construct(ParameterBag $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        return is_string($processable->getValue());
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $value = $processable->getValue();

        return preg_replace_callback('#<\{([a-z0-9_\.-]+)\}>#i', function ($matches) {
            $key = $matches[1];
            if (!$this->parameters->has($key)) {
                throw new \UnexpectedValueException(sprintf(
                    'Parameter "%s" was not found',
                    $key
                ));
            }

            if (is_array($value = $this->parameters->get($key))) {
                return var_export($value, true);
            }

            return $value;
        }, $value);
    }
}
