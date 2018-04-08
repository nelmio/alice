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

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Parser\ParseExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException;

final class JsonParser implements ChainableParserInterface
{
    use IsAServiceTrait;

    private const REGEX = '/.{1,}\.json$/i';

    /**
     * @inheritdoc
     */
    public function canParse(string $file): bool
    {
        if (false === stream_is_local($file)) {
            return false;
        }

        return 1 === preg_match(self::REGEX, $file);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $file Local JSON file
     *
     * @throws UnparsableFileException
     */
    public function parse(string $file): array
    {
        if (false === file_exists($file)) {
            throw InvalidArgumentExceptionFactory::createForFileCouldNotBeFound($file);
        }

        try {
            $data = json_decode(file_get_contents($file), true);

            if (null === $data) {
                throw ParseExceptionFactory::createForInvalidJson($file);
            }

            return $data;
        } catch (\Exception $exception) {
            if ($exception instanceof UnparsableFileException) {
                throw $exception;
            }

            throw ParseExceptionFactory::createForUnparsableFile($file, 0, $exception);
        }
    }
}
