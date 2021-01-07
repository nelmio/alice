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

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

/**
 * Decorates a parser to cache the result and process includes. Includes are being processed in this parser to be able
 * to cache the whole result besides each included file.
 *
 * @TODO: make it PSR-6 or PSR-16 compliant
 */
final class RuntimeCacheParser implements ParserInterface
{
    use IsAServiceTrait;

    /**
     * @var array[] Keys are real path of cached files and the values the resulting array
     */
    private $cache = [];

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var IncludeProcessorInterface
     */
    private $includeProcessor;

    public function __construct(ParserInterface $parser, FileLocatorInterface $fileLocator, IncludeProcessorInterface $includeProcessor)
    {
        $this->parser = $parser;
        $this->fileLocator = $fileLocator;
        $this->includeProcessor = $includeProcessor;
    }
    
    public function parse(string $file): array
    {
        try {
            $realPath = $this->fileLocator->locate($file);
        } catch (FileNotFoundException $exception) {
            throw InvalidArgumentExceptionFactory::createForFileCouldNotBeFound($file, 0, $exception);
        }

        if (array_key_exists($realPath, $this->cache)) {
            return $this->cache[$realPath];
        }

        $data = $this->parser->parse($realPath);

        if (array_key_exists('include', $data)) {
            $data = $this->includeProcessor->process($this, $realPath, $data);
        }

        $this->cache[$realPath] = $data;

        return $data;
    }
}
