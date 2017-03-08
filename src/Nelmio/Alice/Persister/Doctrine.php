<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Persister;

use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\PersisterInterface;

/**
 * Bridge for Doctrine ObjectManager.
 */
class Doctrine implements PersisterInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var bool
     */
    protected $flush;

    /**
     * @var string[]
     */
    protected $persistableClasses;

    /**
     * @param ObjectManager $objectManager
     * @param bool          $doFlush
     */
    public function __construct(ObjectManager $objectManager, $doFlush = true)
    {
        $this->om = $objectManager;
        $this->flush = $doFlush;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(array $objects)
    {
        $persistable = $this->getPersistableClasses();

        foreach ($objects as $object) {
            if (in_array(get_class($object), $persistable)) {
                $this->om->persist($object);
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
            throw new \UnexpectedValueException("Entity with ID {$id} and class {$class} not found");
        }

        return $entity;
    }

    /**
     * @return string[]
     */
    private function getPersistableClasses()
    {
        if (!isset($this->persistableClasses)) {
            $metadatas = $this->om->getMetadataFactory()->getAllMetadata();

            foreach ($metadatas as $metadata) {
                if (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass) {
                    continue;
                }
                if (isset($metadata->isEmbeddedDocument) && $metadata->isEmbeddedDocument) {
                    continue;
                }

                $this->persistableClasses[] = $metadata->getName();
            }
        }

        return $this->persistableClasses;
    }
}
