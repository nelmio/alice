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

use Nelmio\Alice\Persister\PersisterInterface;

interface PersistenceAwareLoaderInterface extends LoaderInterface
{
    /**
     * Sets or unsets the persister to use.
     *
     * @param PersisterInterface|null $persister
     *
     * @return PersistenceAwareLoaderInterface
     */
    public function setPersister(PersisterInterface $persister = null);
}
