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

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Parser\IncludeProcessor\IncludeDataMerger;
use Nelmio\Alice\ParserInterface;

final class SimpleFilesLoader implements FilesLoaderInterface
{
    use IsAServiceTrait;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    /**
     * @var IncludeDataMerger
     */
    private $dataMerger;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser, DataLoaderInterface $dataLoader)
    {
        $this->parser = $parser;
        $this->dataMerger = new IncludeDataMerger();
        $this->dataLoader = $dataLoader;
    }
    
    public function loadFiles(array $files, array $parameters = [], array $objects = []): ObjectSet
    {
        $data = array_reduce(
            array_unique($files),
            function (array $data, string $file): array {
                $fileData = $this->parser->parse($file);

                return $this->dataMerger->mergeInclude($data, $fileData);
            },
            []
        );

        return $this->dataLoader->loadData($data, $parameters, $objects);
    }
}
