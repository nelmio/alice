<?php

namespace Nelmio\Alice\Fixtures;

/**
 * Loads fixtures from an array or file.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads a fixture file.
     *
     * @param string|array $dataOrFilename data array or filename
     *
     * @return array Objects loaded (not persisted).
     */
    public function load($dataOrFilename);
}
