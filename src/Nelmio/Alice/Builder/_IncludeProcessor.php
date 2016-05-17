<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

use Nelmio\Alice\Exception\Builder\RuntimeException;
use Nelmio\Alice\Exception\Builder\TypeException;
use Nelmio\Alice\ParserInterface;

/**
 * @internal
 * @final
 */
class IncludeProcessor
{
    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Checks if the data contains any include statement. If it does, recursively parse the included files and merge
     * their content to the existing data.
     *
     * @param array $data Existing data
     *
     * @throws RuntimeException
     * @throws TypeException
     *
     * @return array
     */
    public function process(array $data): array
    {
        if (false === array_key_exists('meta', $data) || false === isset($data['meta']['file'])) {
            throw new RuntimeException('Could not find the meta file value.');
        }
        $file = $data['meta']['file'];

        if (false === array_key_exists('include', $data)) {
            return $data;
        }

        $includes = $data['include'];
        if (false === is_array($includes)) {
            throw new TypeException(
                sprintf(
                    'Include statement must be an array. Found "%s" instead',
                    gettype($includes)
                )
            );
        }

        foreach ($includes as $include) {
            if (false === is_string($include) || '' === $include) {
                throw new TypeException(
                    sprintf(
                        'Included files must be file names. "%s" is not a file',
                        $include
                    )
                );
            }

            $data = $this->includeFile($this->parser, $file, $data, $include);
        }

        return $data;
    }

    private function includeFile(ParserInterface $parser, string $file, array $data, string $include): array
    {
        $includeFile = ('/' === $file[0])
            ? $include
            : dirname($file).DIRECTORY_SEPARATOR.$include
        ;

        $includeData = $parser->parse($includeFile);
        $newData = $this->process($includeData);

        return $this->mergeData($data, $newData);
    }

    private function mergeData(array $data, array $newData): array
    {
        foreach ($data as $class => $fixtures) {
            $newData[$class] = isset($newData[$class])
                ? array_merge($newData[$class], $fixtures)
                : $fixtures
            ;
        }

        return $newData;
    }
}
