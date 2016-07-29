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
use Nelmio\Alice\NotClonableTrait;

/**
 * Decorates a parser to cache the result and process includes. Includes are being processed in this parser to be able
 * to cache the whole result besides each included file.
 *
 * @TODO: make it PSR-6 or PSR-16 compliant
 */
final class RuntimeCacheParser implements ParserInterface
{
    use NotClonableTrait;

    /**
     * @var array[] Keys are real path of cached files and the values the resulting array
     */
    private $cache = [];

    /**
     * @var ParserInterface[]
     */
    private $parser;

    /**
     * @var IncludeProcessorInterface
     */
    private $includeProcessor;

    public function __construct(ParserInterface $parser, IncludeProcessorInterface $includeProcessor)
    {
        $this->parser = $parser;
        $this->includeProcessor = $includeProcessor;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $file): array
    {
        $cacheResult = true;
        $realPath = realpath($file);

        // If realpath() returns false, $realPath is safely casted into an integer (i.e. an array key)
        if (isset($this->cache[$realPath])) {
            return $this->cache[$realPath];
        }

        // $file could not be resolved. This either means the file does not exist or is not local.
        // This doesn't mean the file is impossible to parse, hence it is passed to the decorated
        // parser without caching the result.
        if (false === $realPath) {
            $cacheResult = false;
            $realPath = $file;
        }

        $data = $this->parser->parse($realPath);
        if (array_key_exists('include', $data)) {
            $data = $this->includeProcessor->process($this, $file, $data);
        }

        if ($cacheResult) {
            $this->cache[$realPath] = $data;
        }

        return $data;
    }
}
