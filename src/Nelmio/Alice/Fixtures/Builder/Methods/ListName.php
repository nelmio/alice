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

class ListName implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canBuild($name)
    {
        if (1 !== preg_match('/\{(?<content>.*)\}/', $name, $matches)) {
            return false;
        }

        $content = $matches['content'];
        if (false === strpos($content, ',')) {
            // is not a list but can be a single element e.g. user{alice}
            if (false !== strpos($content, '..')) {
                return false;
            }

            return 1 !== preg_match('/\{\d+.\d+/', $name);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function build($class, $name, array $spec)
    {
        if (1 !== preg_match('/\{(?<content>[^,\s]+(?:,\s[^,\s]+)+)\}/', $name, $matches)) {
            preg_match('/\{(?<content>.*)\}/', $name, $matches);

            @trigger_error(
                sprintf(
                    'You have a malformed ranged list "%s". Ranged list must follow the mask "user_{alice, bob}". '
                    .'Constructing malformed ranged list is deprecated since 2.2.0 and will throw an error in Alice 3.0.',
                    $name
                ),
                E_USER_DEPRECATED
            );
        }
        $fixtures = [];

        $enumItems = array_map('trim', explode(',', $matches['content']));
        foreach ($enumItems as $itemName) {
            if ('' === $itemName) {
                continue;
            }

            $currentName = str_replace($matches[0], $itemName, $name);
            $fixtures[] = new Fixture($class, $currentName, $spec, $itemName);
        }

        return $fixtures;
    }
}
