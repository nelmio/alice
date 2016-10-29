<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Entity\Hydrator;

class Dummy
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;

    private $publicAccessor;
    private $publicMethodAccessor;
    private $publicGetSetter;
    private $publicAccessorWithDefaultValue;
    private $publicAccessorWithRequiredAndDefaultValue;
    private $publicAccessorWithMoreRequiredParameters;
    private $publicIsAccessor;
    private $publicHasAccessor;
    private $publicGetter;
    private $date;

    public function setPublicAccessor($value)
    {
        $this->publicAccessor = $value;
    }

    public function setPublicAccessorWithDefaultValue($value = null)
    {
        $this->publicAccessorWithDefaultValue = $value;
    }

    public function setPublicAccessorWithRequiredAndDefaultValue($value, $optional = null)
    {
        $this->publicAccessorWithRequiredAndDefaultValue = $value;
    }

    public function setPublicAccessorWithMoreRequiredParameters($value, $needed)
    {
        $this->publicAccessorWithMoreRequiredParameters = $value;
    }

    public function getPublicAccessor()
    {
        return $this->publicAccessor;
    }

    public function getPublicAccessorWithDefaultValue()
    {
        return $this->publicAccessorWithDefaultValue;
    }

    public function getPublicAccessorWithRequiredAndDefaultValue()
    {
        return $this->publicAccessorWithRequiredAndDefaultValue;
    }

    public function getPublicAccessorWithMoreRequiredParameters()
    {
        return $this->publicAccessorWithMoreRequiredParameters;
    }

    public function setPublicIsAccessor($value)
    {
        $this->publicIsAccessor = $value;
    }

    public function isPublicIsAccessor()
    {
        return $this->publicIsAccessor;
    }

    public function setPublicHasAccessor($value)
    {
        $this->publicHasAccessor = $value;
    }

    public function hasPublicHasAccessor()
    {
        return $this->publicHasAccessor;
    }

    public function publicGetSetter($value = null)
    {
        if (null !== $value) {
            $this->publicGetSetter = $value;
        }

        return $this->publicGetSetter;
    }

    public function getPublicMethodMutator()
    {
        return $this->publicGetSetter;
    }

    protected function setProtectedAccessor($value)
    {
    }

    protected function getProtectedAccessor()
    {
        return 'foobar';
    }

    protected function setProtectedIsAccessor($value)
    {
    }

    protected function isProtectedIsAccessor()
    {
        return 'foobar';
    }

    protected function setProtectedHasAccessor($value)
    {
    }

    protected function hasProtectedHasAccessor()
    {
        return 'foobar';
    }

    private function setPrivateAccessor($value)
    {
    }

    private function getPrivateAccessor()
    {
        return 'foobar';
    }

    private function setPrivateIsAccessor($value)
    {
    }

    private function isPrivateIsAccessor()
    {
        return 'foobar';
    }

    private function setPrivateHasAccessor($value)
    {
    }

    private function hasPrivateHasAccessor()
    {
        return 'foobar';
    }

    public function getPublicGetter()
    {
        return $this->publicGetter;
    }

    public function setDate(\DateTimeInterface $date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }
}
