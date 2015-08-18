<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\Fixtures\LoaderInterface;
use Nelmio\Alice\Persister\Doctrine as DoctrinePersister;
use Psr\Log\LoggerInterface;

/**
 * Bootstraps the given loader to persist the objects retrieved by the loader.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FixturesLoader
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @param PersisterInterface|ObjectManager $container
     * @param LoaderInterface                  $loader
     * @param ProcessorInterface[]             $processors
     */
    public function __construct($container, LoaderInterface $loader, array $processors = [])
    {
        if ($container instanceof ObjectManager) {
            $this->persister = new DoctrinePersister($container);
        } else {
            throw new \InvalidArgumentException('Unknown container type '.get_class($this->persister));
        }

        $this->loader = $loader;
        if ($loader instanceof Loader) {
            $this->loader->setPersister($this->persister);
        }

        foreach ($processors as $processor) {
            if (false === $processor instanceof ProcessorInterface) {
                throw new \InvalidArgumentException('Expected processor to implement Nelmio\Alice\ProcessorInterface');
            }
        }
        $this->processors = $processors;
    }

    /**
     * Loads fixtures file and persist the retrieved objects.
     *
     * @param string|array $files filename, glob mask (e.g. *.yml) or array of filenames to load data from, or data
     *                            array
     * @param bool         $persist_once
     *
     * @return array Persisted objects.
     */
    public function load($files, $persist_once = false)
    {
        // glob strings to filenames
        if (!is_array($files)) {
            $matches = glob($files, GLOB_BRACE);
            if (!$matches && !file_exists($files)) {
                throw new \InvalidArgumentException('The file could not be found: '.$files);
            }
            $files = $matches;
        }

        // wrap the data array in an array of one data array
        if (!is_string(current($files))) {
            $files = [$files];
        }

        $objects = [];
        foreach ($files as $file) {
            $set = $this->loader->load($file);

            if (false === $persist_once) {
                $this->persist($set);
            }

            $objects = array_merge($objects, $set);
        }

        if (true === $persist_once) {
            $this->persist($objects);
        }

        return $objects;
    }

    /**
     * Use the Fixture persister to persist objects and calling the processors.
     *
     * @param object[] $objects
     */
    protected function persist($objects)
    {
        foreach ($this->processors as $processor) {
            foreach ($objects as $obj) {
                $processor->preProcess($obj);
            }
        }

        $this->persister->persist($objects);

        foreach ($this->processors as $processor) {
            foreach ($objects as $obj) {
                $processor->postProcess($obj);
            }
        }
    }
}
