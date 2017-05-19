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
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class PhpParser implements ChainableParserInterface
{
    use IsAServiceTrait;

    /** @interval */
    const REGEX = '/.+\.php[7]?$/i';

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
     * @param string $file Local PHP file
     */
    public function parse(string $file): array
    {
        if (false === file_exists($file)) {
            throw InvalidArgumentExceptionFactory::createForFileCouldNotBeFound($file);
        }

        $data = include $file;

        if (false === is_array($data)) {
            throw TypeErrorFactory::createForInvalidFixtureFileReturnedData($file);
        }

        return $data;
    }
}
