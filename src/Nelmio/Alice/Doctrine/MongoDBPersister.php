<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Doctrine;

use Nelmio\Alice\Doctrine\MongoDBInterface;
use Nelmio\Alice\Doctrine\BasePersister;

/**
 * The Doctrine persists the fixtures into an ObjectManager
 */
class MongoDBPersister extends BasePersister implements MongoDBInterface
{
}
