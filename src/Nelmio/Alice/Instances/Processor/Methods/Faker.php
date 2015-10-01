<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class Faker implements MethodInterface
{
    /**
     * @var Collection
     */
    protected $objects;

    /**
     * Custom faker providers to use with faker generator
     *
     * @var array
     */
    private $providers;

    /**
     * @var \Faker\Generator[]
     */
    private $generators = [];

    /**
     * Default locale to use with faker
     *
     * @var string
     */
    private $defaultLocale;

    /**
     * @var string
     */
    private $valueForCurrent;

    public function __construct(array $providers, $locale = 'en_US')
    {
        $this->providers     = $providers;
        $this->defaultLocale = $locale;
    }

    /**
     * sets the object collection to handle referential calls
     *
     * @param Collection $objects
     */
    public function setObjects(Collection $objects)
    {
        $this->objects = $objects;
    }

    /**
     * sets the value for <current()>
     *
     * @param string
     */
    public function setValueForCurrent($valueForCurrent)
    {
        $this->valueForCurrent = $valueForCurrent;
    }

    /**
     * sets the providers that can be used
     *
     * @param array
     */
    public function setProviders(array $providers)
    {
        $this->providers = $providers;
        $this->generators = [];
    }

    /**
     * Adds one or more providers that can be used
     *
     * @param object|array $provider Provider or array of providers
     */
    public function addProvider($provider)
    {
        if (!is_array($provider)) {
            $provider = [$provider];
        }
        foreach ($provider as $p) {
            $this->providers[] = $p;
            foreach ($this->generators as $generator) {
                $generator->addProvider($p);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        return is_string($processable->getValue());
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $fakerRegex = '<(?:(?<locale>[a-z]+(?:_[a-z]+)?):)?(?<name>[a-z0-9_]+?)?\((?<args>(?:[^)]*|\)(?!>))*)\)>';
        if ($processable->valueMatches('#^'.$fakerRegex.'$#i')) {
            return $this->replacePlaceholder($processable->getMatches(), $variables);
        } else {
            // format placeholders inline
            return preg_replace_callback('#'.$fakerRegex.'#i', function ($matches) use ($variables) {
                return $this->replacePlaceholder($matches, $variables);
            }, $processable->getValue());
        }
    }

    /**
     * replaces a placeholder by the result of a ->fake call
     *
     * @param  array $matches
     * @param  array $variables
     * @return mixed
     */
    public function replacePlaceholder($matches, array $variables)
    {
        $args = isset($matches['args']) && '' !== $matches['args'] ? $matches['args'] : null;

        if (trim($matches['name']) == '') {
            $matches['name'] = 'identity';
        }

        if (!$args) {
            return $this->fake($matches['name'], $matches['locale']);
        }

        // replace references to other variables in the same object
        $args = preg_replace_callback('{\{?\$([a-z0-9_]+)\}?}i', function ($match) use ($variables) {
            if (array_key_exists($match[1], $variables)) {
                return '$variables['.var_export($match[1], true).']';
            }

            return $match[0];
        }, $args);

        // replace references to other objects
        $args = preg_replace_callback('{(?<string>".*?[^\\\\]")|(?:(?<multi>\d+)x )?(?<!\\\\)@(?<reference>[a-z0-9_.*]+)(?:\->(?<property>[a-z0-9_-]+))?}i', function ($match) {

            if (!empty($match['string'])) {
                return $match['string'];
            }

            $multi    = ('' !== $match['multi']) ? $match['multi'] : null;
            $property = isset($match['property']) ? $match['property'] : null;
            if (strpos($match['reference'], '*')) {
                return '$this->objects->random(' . var_export($match['reference'], true) . ', ' . var_export($multi, true) . ', ' . var_export($property, true) . ')';
            }
            if (null !== $multi) {
                throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$match['multi'].'x @user*", otherwise you would always get only one item.');
            }

            return '$this->objects->find(' . var_export($match['reference'], true) . ', ' . var_export($property, true) . ')';
        }, $args);

        $locale = var_export($matches['locale'], true);
        $name = var_export($matches['name'], true);

        // enable calls to $fake() to call faker from within faker calls
        $that = $this;
        $fake = function () use ($that) {
            return call_user_func_array([$that, 'fake'], func_get_args());
        };

        return eval('return $this->fake(' . $name . ', ' . $locale . ', ' . $args . ');');
    }

    /**
     * Returns a fake value
     *
     * This is made public so it is accessible by the $fake() callback in replacePlaceholder
     * and the callback in Parser\Method\Base::createFakerClosure
     *
     * @param string $formatter
     * @param string $locale
     * @private
     * @return mixed
     */
    public function fake($formatter, $locale = null)
    {
        $args = array_slice(func_get_args(), 2);

        if ($formatter == 'current') {
            if ($this->valueForCurrent === null) {
                throw new \UnexpectedValueException('Cannot use <current()> out of fixtures ranges or enum');
            }

            return $this->valueForCurrent;
        }

        return $this->getGenerator($locale)->format($formatter, $args);
    }

    /**
     * Get the generator for this locale
     *
     * @param string $locale the requested locale, defaults to constructor injected default
     *
     * @return \Faker\Generator the generator for the requested locale
     */
    private function getGenerator($locale = null)
    {
        $locale = $locale ?: $this->defaultLocale;

        if (!isset($this->generators[$locale])) {
            $generator = \Faker\Factory::create($locale);
            foreach ($this->providers as $provider) {
                $generator->addProvider($provider);
            }
            $this->generators[$locale] = $generator;
        }

        return $this->generators[$locale];
    }
}
