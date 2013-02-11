<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\ORMInterface;
use Nelmio\Alice\PersisterInterface;

/**
 * The Doctrine persists the fixtures into an ObjectManager
 */
class Doctrine implements ORMInterface
{
    private $om;
    private $flush;
    private $persisters = array();

    public function __construct(ObjectManager $om, $doFlush = true, $persisters = array())
    {
        $this->om = $om;
        $this->flush = $doFlush;

        foreach ($persisters as $name => $persister) {
            $this->addPersister($name, $persister);
        }
    }

    public function addPersister($name, PersisterInterface $persister)
    {
        $this->persisters[$name] = $persister;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(array $objects)
    {
        foreach ($objects as $object) {
            if (array_key_exists($object['persister'], $this->persisters)) {
                $this->persisters[$object['persister']]->persist($object['object']);
            } else {
                $this->om->persist($object['object']);
            }
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
