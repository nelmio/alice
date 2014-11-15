<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use InvalidArgumentException;
use Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface;

class Builder
{
    /**
     * @var array
     **/
    protected $methods;

    /**
     * @var array
     */
    protected $templates;

    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            if (!($method instanceof MethodInterface)) {
                throw new InvalidArgumentException("All methods passed into Builder must implement MethodInterface.");
            }
        }

        $this->methods = $methods;
        $this->templates = [];
    }

    /**
     * adds a builder for fixture building extensions
     *
     * @param MethodInterface $builder
     **/
    public function addBuilder(MethodInterface $builder)
    {
        array_unshift($this->methods, $builder);
    }

    /**
     * builds a single fixture from a "raw" definition
     *
     * @param array $rawData
     */
    public function build($class, $name, array $spec)
    {
        foreach ($this->methods as $method) {
            if ($method->canBuild($name)) {
                $fixtures = $method->build($class, $name, $spec);

                $indexesToRemove = [];
                foreach ($fixtures as $index => $fixture) {
                    if ($fixture->hasExtensions()) {
                        foreach ($fixture->getExtensions() as $extension) {
                            $fixture->extendTemplate($this->getTemplate($extension));
                        }
                    }

                    if ($fixture->isTemplate()) {
                        $this->templates[$fixture->getName()] = $fixture;
                        $indexesToRemove[] = $index;
                    }
                }

                foreach (array_reverse($indexesToRemove) as $index) {
                    array_splice($fixtures, $index, 1);
                }

                return $fixtures;
            }
        }
    }

    /**
     * returns the template with the given name
     *
     * @param  string  $name
     * @return Fixture
     */
    protected function getTemplate($name)
    {
        if (!(isset($this->templates[$name]) || array_key_exists($name, $this->templates))) {
            throw new \UnexpectedValueException('Template '.$name.' is not defined.');
        }

        return $this->templates[$name];
    }
}
