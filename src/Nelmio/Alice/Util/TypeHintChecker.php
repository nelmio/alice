<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Util;

use Nelmio\Alice\PersisterInterface;

class TypeHintChecker
{
    /**
     * @var PersisterInterface|null
     */
    protected $manager;

    /**
     * @param PersisterInterface $manager
     */
    public function setPersister(PersisterInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Checks if the value is typehinted with a class and if the current value can be coerced into that type. It can
     * either convert to datetime or attempt to fetched from the database by ID.
     *
     * @param object|string $object Instance or FQCN to which the value is being set
     * @param string        $method Method used to set the checked value (constructor or setter for example)
     * @param mixed         $value  Value to check
     * @param integer       $parameterNumber
     *
     * @return mixed
     */
    public function check($object, $method, $value, $parameterNumber = 0)
    {
        if (!is_numeric($value) && !is_string($value)) {
            return $value;
        }

        $reflection = new \ReflectionMethod($object, $method);
        $params = $reflection->getParameters();

        if (false === array_key_exists($parameterNumber, $params) || null === $params[$parameterNumber]->getClass()) {
            return $value;
        }

        $hintedClass = $params[$parameterNumber]->getClass()->getName();
        if ('DateTime' === $hintedClass) {
            return $this->createDate($value, $reflection, $method);
        }

        if ($hintedClass) {
            if (!$this->manager) {
                throw new \LogicException('To reference objects by id you must first set a Nelmio\Alice\PersisterInterface object on this instance');
            }

            return $this->manager->find($hintedClass, $value);
        }

        return $value;
    }

    /**
     * @param string            $value
     * @param \ReflectionMethod $reflectionMethod
     * @param string            $method
     *
     * @return \DateTime
     */
    private function createDate($value, \ReflectionMethod $reflectionMethod, $method)
    {
        @trigger_error(
            'Casting a string date into a DateTime object is deprecated since 2.3.0 and will be removed in Alice 3.0. Create'
            .'a DateTime object directly by using the identity function like "<(new \DateTime(\'2012-01-05\'))>" '
            .'instead.',
            E_USER_DEPRECATED
        );

        try {
            if (preg_match('/^[0-9]+$/', $value)) {
                $value = '@'.$value;
            }

            return new \DateTime($value);
        } catch (\Exception $exception) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Could not convert %s to DateTime for %s::%s',
                    $value,
                    $reflectionMethod->getDeclaringClass()->getName(),
                    $method
                ),
                0,
                $exception
            );
        }
    }
}
