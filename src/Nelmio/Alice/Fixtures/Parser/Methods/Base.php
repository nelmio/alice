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
     * The context allows any kind of contextual information to be available in fixtures
     *
     * @var mixed
     **/
    protected $context;

    /**
     * @var string
     **/
    protected $extension = null;

    public function __construct($context = null)
    {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function canParse($file)
    {
        // we add (\.php)? to the regex to allow extensions of this parser to first
        // be compiled by php
        return preg_match("/\.{$this->extension}(\.php)?$/", $file) == 1;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function parse($file);

    /**
     * Returns a string of text after compiling all the PHP code in the fixture
     *
     * @param string $file
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
        $data = $includeWrapper();

        return ob_get_clean();
    }

    protected function createFakerClosure()
    {
        if (!$this->context instanceof Loader) {
            return;
        }
        $faker = $this->context->getFakerProcessorMethod();

        return function () use ($faker) {
            return call_user_func_array(array($faker, 'fake'), func_get_args());
        };
    }
}
