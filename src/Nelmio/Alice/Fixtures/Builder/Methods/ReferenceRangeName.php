<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Collection;

final class ReferenceRangeName implements MethodInterface
{
    /**
     * @var array $matches
     */
    private $matches = [];

    /**
     * @var Collection
     */
    private $objects;

    /**
     * @param Collection $objects
     */
    public function __construct(Collection $objects)
    {
        $this->objects = $objects;
    }

    /**
     * @inheritDoc
     */
    public function canBuild($name)
    {
        return 1 === preg_match('#\{@([0-9a-zA-Z\._\-]+)(\*?+)\}#i', $name, $this->matches);
    }

    /**
     * @inheritDoc
     */
    public function build($class, $name, array $spec)
    {
        // Could be 'car1' from engine_{@car1}
        $referenceName = $this->matches[1];
        $referenceAll = '*' === $this->matches[2];

        if ($referenceAll) {
            return $this->buildAll($class, $name, $spec, $referenceName);
        }

        $fixtures = [];

        $currentValue = $this->objects->get($this->matches[1]);
        if (null === $currentValue) {
            throw new \UnexpectedValueException(
                sprintf('Instance %s is not defined!', $this->matches[1])
            );
        }
        $currentName = str_replace($this->matches[0], $referenceName, $name);

        $fixtures[] = new Fixture($class, $currentName, $spec, $currentValue);

        return $fixtures;
    }

    /**
     * @param string $class
     * @param string $name
     * @param array  $spec
     * @param string $referenceName
     *
     * @return Fixture[]
     */
    private function buildAll($class, $name, array $spec, $referenceName)
    {
        $fixtures = [];

        $keys = $this->objects->getKeysByMask($referenceName . "*");

        if (0 === count($keys)) {
            throw new \UnexpectedValueException(
                sprintf('No instances for %s defined!', $this->matches[1])
            );
        }

        foreach ($keys as $currentIndex => $key) {
            $instance = $this->objects->find($key);
            $currentName = str_replace($this->matches[0], $key, $name);

            $fixtures[] = new Fixture($class, $currentName, $spec, $instance);
        }

        return $fixtures;
    }
}
