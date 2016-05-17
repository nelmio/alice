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

use Nelmio\Alice\Exception\Parser\InvalidArgumentException;
use Nelmio\Alice\ParserInterface;

/**
 * Decorates a parser to cache the result and process includes.
 */
final class RuntimeCacheParser implements ParserInterface
{
    /**
     * @var array[] Keys are real path of cached files and the values the resulting array
     */
    private $cache = [];

    /**
     * @var IncludeDataMerger
     */
    private $includeMerger;

    /**
     * @var ParserInterface[]
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
        $this->includeMerger = new IncludeDataMerger();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
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
            $data = $this->processInclude($file, $data);
        }

        if ($cacheResult) {
            $this->cache[$realPath] = $data;
        }

        return $data;
    }

    /**
     * @param string $file File loaded
     * @param array  $data Parse result of the loaded file
     *
     * @throws InvalidArgumentException
     * 
     * @return array
     */
    private function processInclude(string $file, array $data): array
    {
        $include = $data['include'];
        unset($data['include']);
        
        if (null === $include) {
            return $data;
        }

        if (false === is_array($include)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected include statement to be either null or an array of files to include. Got %s instead in '
                    .'file "%s".',
                    gettype($include),
                    $file
                )
            );
        }

        foreach ($include as $includeFile) {
            if (false === is_string($includeFile)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected elements of include statement to be file names. Got %s instead in file "%s".',
                        gettype($includeFile),
                        $file
                    )
                );
            }

            if (0 === strlen($includeFile)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected elements of include statement to be file names. Got empty string instead in file '
                        .'"%s".',
                        $file
                    )
                );
            }

            $includeFilePath = $this->getIncludeFilePath($file, $includeFile);
            $includeData = $this->parse($includeFilePath);
            
            $data = $this->includeMerger->mergeInclude($data, $includeData);
        }
        
        return $data;
    }

    /**
     * Resolves the path of the file to include.
     * 
     * @param string $file
     * @param string $includeFile Non empty string
     *
     * @return string
     */
    private function getIncludeFilePath(string $file, string $includeFile): string
    {
        if ('/' === $includeFile[0]) {
            return $includeFile;
        }

        return dirname($file).DIRECTORY_SEPARATOR.$includeFile;
    }
}
