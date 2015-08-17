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
     * PersisterInterface
     */
    protected $manager;

    /**
     * public interface to set the Persister interface
     *
     * @param PersisterInterface $manager
     */
    public function setPersister(PersisterInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
   * Checks if the value is typehinted with a class and if the current value can be coerced into that type
   *
   * It can either convert to datetime or attempt to fetched from the db by id
   *
   * @param  mixed   $obj    instance or class name
   * @param  string  $method
   * @param  string  $value
   * @param  integer $pNum
   * @return mixed
   */
    public function check($obj, $method, $value, $pNum = 0)
    {
        if (!is_numeric($value) && !is_string($value)) {
            return $value;
        }

        $reflection = new \ReflectionMethod($obj, $method);
        $params = $reflection->getParameters();

        if (!isset($params[$pNum]) || !$params[$pNum]->getClass()) {
            return $value;
        }

        $hintedClass = $params[$pNum]->getClass()->getName();

        if ($hintedClass === 'DateTime') {
            try {
                if (preg_match('{^[0-9]+$}', $value)) {
                    $value = '@'.$value;
                }

                return new \DateTime($value);
            } catch (\Exception $e) {
                throw new \UnexpectedValueException('Could not convert '.$value.' to DateTime for '.$reflection->getDeclaringClass()->getName().'::'.$method, 0, $e);
            }
        }

        if ($hintedClass) {
            if (!$this->manager) {
                throw new \LogicException('To reference objects by id you must first set a Nelmio\Alice\PersisterInterface object on this instance');
            }
            $value = $this->manager->find($hintedClass, $value);
        }

        return $value;
    }
}
