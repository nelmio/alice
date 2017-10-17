<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser\Methods;

use Nelmio\Alice\Fixtures\Loader;

abstract class Base implements MethodInterface
{
    /**
     * The context allows any kind of contextual information to be available in fixtures.
     *
     * @var mixed
     **/
    protected $context;

    /**
     * @var string e.g. php, yaml, etc.
     **/
    protected $extension = null;

    /**
     * @param null $context Allows any kind of contextual information to be available in fixtures.
     */
    public function __construct($context = null)
    {
        if (null !== $context && false === $context instanceof Loader) {
            @trigger_error(
                'Passing context in the parser is deprecated since 2.2.0 and will be removed in Alice 3.0.',
                E_USER_DEPRECATED
            );
        }

        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function canParse($file)
    {
        // we add (\.php)? to the regex to allow extensions of this parser to first
        // be compiled by php
        return 1 === preg_match("/\\.{$this->extension}(\\.php)?$/", $file);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function parse($file);

    /**
     * Returns a string of text after compiling all the PHP code in the fixture
     *
     * @param string $file
     *
     * @return string
     */
    protected function compilePhp($file)
    {
        $context = $this->context;

        ob_start();
        $fake = $this->createFakerClosure();
        $includeWrapper = function () use ($file, $context, $fake) {
            return include $file;
        };
        $includeWrapper();

        return ob_get_clean();
    }

    /**
     * @return \Closure|null
     */
    protected function createFakerClosure()
    {
        if (!$this->context instanceof Loader) {
            return null;
        }
        $faker = $this->context->getFakerProcessorMethod();

        return function () use ($faker) {
            return call_user_func_array([$faker, 'fake'], func_get_args());
        };
    }

    /**
     * @param array  $data
     * @param string $filename
     *
     * @return mixed
     */
    protected function processIncludes($data, $filename)
    {
        if (isset($data['include'])) {
            foreach ($data['include'] as $include) {
                $includeFile = dirname($filename) . DIRECTORY_SEPARATOR . $include;
                $includeData = $this->parse($includeFile);

                $data = $this->mergeIncludeData($data, $includeData);
            }
        }

        unset($data['include']);

        return $data;
    }

    /**
     * Merges a parsed file parameters with another. If some data overlaps, the existent data is kept.
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function processParameters(array $data)
    {
        if (isset($data['parameters']) && $this->context instanceof Loader) {
            /* @var Loader $loader */
            $loader = $this->context;

            $parameterBag = $loader->getParameterBag();
            foreach ($data['parameters'] as $name => $value) {
                $parameterBag->set($name, $value);
            }
        }

        unset($data['parameters']);

        return $data;
    }

    /**
     * Merges a parsed file data with another. If some data overlaps, the existent data is kept.
     *
     * @param array $data        Parsed file data
     * @param array $includeData Parsed file data to merge
     *
     * @return array
     */
    protected function mergeIncludeData($data, $includeData)
    {
        if (false === is_array($data)) {
            return $includeData;
        }

        if (false === is_array($includeData)) {
            return $data;
        }

        $newData = $includeData;
        foreach ($data as $class => $fixtures) {
            $newData[$class] = isset($newData[$class]) && is_array($newData[$class])
                ? array_merge($newData[$class], $fixtures)
                : $fixtures
            ;
        }

        return $newData;
    }
}
