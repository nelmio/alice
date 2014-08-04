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

use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Doctrine\StorageInterface;

/**
 * The Base Doctrine Persister. Persists the fixtures into an ObjectManager
 */
abstract class BasePersister implements StorageInterface
{
    protected $om;
    protected $flush;

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

    /**
     * {@inheritDoc}
     */
    public function find($class, $id)
    {
        $entity = $this->om->find($class, $id);

        if (!$entity) {
            throw new \UnexpectedValueException('Entity with Id ' . $id . ' and Class ' . $class . ' not found');
        }

        return $entity;
    }
}
