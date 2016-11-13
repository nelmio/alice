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

namespace Nelmio\Alice\Throwable\Exception\Parser;

use Nelmio\Alice\Throwable\ParseThrowable;

//TODO: decompose this exception in several more appropriate functions?
class ParseException extends \Exception implements ParseThrowable
{
    /**
     * @return static
     */
    public static function createForInvalidYaml(string $file, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf('The file "%s" does not contain valid YAML.', $file),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function createForUnparsableFile(string $file, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf('Could not parse the file "%s".', $file),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function createForUnlocalizableFile(string $file, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf('Could not locate the file "%s".', $file),
            $code,
            $previous
        );
    }
}
