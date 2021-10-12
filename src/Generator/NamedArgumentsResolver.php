<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Generator;

use ReflectionException;
use ReflectionMethod;
use RuntimeException;

class NamedArgumentsResolver
{
    public function resolveArguments(array $arguments, string $className, string $methodName): array
    {
        try {
            $method = new ReflectionMethod($className, $methodName);
        } catch (ReflectionException $exception) {
            return $arguments;
        }

        if (0 === count($method->getParameters())) {
            return $arguments;
        }

        $sortedArguments = [];
        $buffer = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            if ($parameter->isVariadic() && [] !== $arguments) {
                $sortedArguments = array_merge($sortedArguments, $buffer, array_values($arguments));
                $arguments = [];
                $buffer = [];

                break;
            }

            if (array_key_exists($name, $arguments)) {
                $sortedArguments = array_merge($sortedArguments, $buffer, [$name => $arguments[$name]]);
                unset($arguments[$name]);
                $buffer = [];

                continue;
            }

            foreach ($arguments as $key => $value) {
                if (is_int($key)) {
                    $sortedArguments = array_merge($sortedArguments, $buffer, [$arguments[$key]]);
                    unset($arguments[$key]);
                    $buffer = [];

                    continue 2;
                }
            }

            if (!$parameter->isDefaultValueAvailable()) {
                if ($parameter->isVariadic()) {
                    continue;
                }

                throw new RuntimeException(sprintf(
                    'Argument $%s of %s::%s() is not passed a value and does not define a default one.',
                    $name,
                    $className,
                    $methodName
                ));
            }

            $buffer[] = $parameter->getDefaultValue();
        }

        $unknownNamedParameters = array_filter(array_keys($arguments), static function ($key) {
            return is_string($key);
        });

        if ([] !== $unknownNamedParameters) {
            throw new RuntimeException(sprintf(
                'Unknown arguments for %s::%s(): $%s.',
                $className,
                $methodName,
                implode(', $', $unknownNamedParameters)
            ));
        }

        return $sortedArguments;
    }
}
