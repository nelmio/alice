<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\ParserInterface;

/**
 * Decorates a parser to cache the result.
 */
final class RuntimeCacheParser implements ParserInterface
{
    /**
     * @var ParserInterface[]
     */
    private $parser;

    /**
     * @var array[] Keys are real path of cached files and the values the resulting array
     */
    private $cache = [];

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $file): array
    {
        $realPath = realpath($file);

        // If realpath() returns false, $realPath is safely casted into an integer
        if (isset($this->cache[$realPath])) {
            return $this->cache[$realPath];
        }

        // $file could not be resolved. This either means the file does not exist or is not local.
        // This doesn't mean the file is impossible to parse, hence it is passed to the decorated
        // parser without caching the result.
        if (false === $realPath) {
            return $this->parser->parse($file);
        }

        $data = $this->parser->parse($realPath);
        $this->cache[$realPath] = $data;

        return $data;
    }
}
