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

/**
 * @private
 */
final class ParseExceptionFactory
{
    public static function createForParserNoFoundForFile(string $file): ParserNotFoundException
    {
        return new ParserNotFoundException(
            sprintf(
                'No suitable parser found for the file "%s".',
                $file
            )
        );
    }

    public static function createForUnparsableFile(string $file, int $code = 0, \Throwable $previous = null): UnparsableFileException
    {
        return new UnparsableFileException(
            sprintf(
                'Could not parse the file "%s".',
                $file
            ),
            $code,
            $previous
        );
    }

    public static function createForInvalidYaml(string $file, int $code = 0, \Throwable $previous = null): UnparsableFileException
    {
        return new UnparsableFileException(
            sprintf(
                'The file "%s" does not contain valid YAML.',
                $file
            ),
            $code,
            $previous
        );
    }

    public static function createForInvalidJson(string $file, int $code = 0, \Throwable $previous = null): UnparsableFileException
    {
        return new UnparsableFileException(
            sprintf(
                'The file "%s" does not contain valid JSON.',
                $file
            ),
            $code,
            $previous
        );
    }

    private function __construct()
    {
    }
}
