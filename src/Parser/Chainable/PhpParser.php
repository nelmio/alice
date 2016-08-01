<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Parser\ChainableParserInterface;

final class PhpParser implements ChainableParserInterface
{
    use NotClonableTrait;

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
            throw new \InvalidArgumentException(sprintf('The file "%s" could not be found.', $file));
        }

        $data = include($file);

        if (false === is_array($data)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must return a PHP array.', $file));
        }

        return $data;
    }
}
