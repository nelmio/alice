<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

final class Fixture
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $specs;

    /**
     * @var string
     */
    private $valueForCurrent;

    /**
     * @var bool
     */
    private $isTemplate;
    /**
     * @var array
     */
    private $extends;

    /**
     * @param string   $className
     * @param string   $name
     * @param array    $specs
     * @param string   $valueForCurrent - when <current()> is called, this value is used
     * @param bool     $isTemplate
     * @param string[] $extends         Templates extended
     *
     * @example
     *  For a fixture:
     *      'Nelmio\Alice\Entity\User' => [
     *          'user0 (extends user_base)' => [
     *              'username' => '<name()>'
     *          ]
     *      ]
     *
     *  The corresponding Fixture is:
     *      $className =  'Nelmio\Alice\Entity\User'
     *      $name = 'user0'
     *      $specs = [
     *          'username' => '<name()>',
     *      ]
     *      $valueForCurrent = null
     *      $isTemplate = false
     *      $extends = [
     *          'user_base',
     *      ]
     */
    public function __construct(
        string $className,
        string $name,
        array $specs,
        string $valueForCurrent = null,
        bool $isTemplate = false,
        array $extends = []
    ) {
        $this->className = $className;
        $this->name = $name;
        $this->specs = $specs;
        $this->valueForCurrent = $valueForCurrent;
        $this->isTemplate = $isTemplate;
        $this->extends = $extends;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpecs(): array
    {
        return $this->specs;
    }

    /**
     * @return string|null
     */
    public function getValueForCurrent()
    {
        return $this->valueForCurrent;
    }

    public function isTemplate(): bool
    {
        return $this->isTemplate;
    }

    public function getExtends(): array
    {
        return $this->extends;
    }
}
