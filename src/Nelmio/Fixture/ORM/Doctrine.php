<?php

/*
 * This file is part of the Nelmio Fixture package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Fixture\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Fixture\ORMInterface;

/**
 * The Doctrine persists the fixtures into an ObjectManager
 */
class Doctrine implements ORMInterface
{
    private $om;
    private $flush;

    public function __construct(ObjectManager $om, $doFlush = true)
    {
        $this->om = $om;
        $this->flush = $doFlush;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(array $objects)
    {
        foreach ($objects as $object) {
            $this->om->persist($object);
        }

        if ($this->flush) {
            $this->om->flush();
        }
    }
}
