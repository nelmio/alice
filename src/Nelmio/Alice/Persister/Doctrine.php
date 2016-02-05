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
 * Adaptor for the ObjectManager to provide a Doctrine bridge.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Robert Schönthal
 */
class Doctrine implements PersisterInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var bool
     */
    protected $flush;

    /**
     * @param ObjectManager $manager
     * @param bool          $flush
     */
    public function __construct(ObjectManager $manager, $flush = true)
    {
        $this->manager = $manager;
        $this->flush = $flush;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(array $objects)
    {
        foreach ($objects as $object) {
            $this->manager->persist($object);
        }

        if ($this->flush) {
            $this->manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function find($class, $id)
    {
        return $this->manager->find($class, $id);
    }
}
