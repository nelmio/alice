<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;


use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParserInterface;

final class SimpleFileLoader implements FileLoaderInterface
{
    use NotClonableTrait;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser, DataLoaderInterface $dataLoader)
    {
        $this->parser = $parser;
        $this->dataLoader = $dataLoader;
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $data = $this->parser->parse($file);

        return $this->dataLoader->loadData($data, $parameters, $objects);
    }
}
