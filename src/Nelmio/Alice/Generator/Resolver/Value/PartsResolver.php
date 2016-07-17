<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;

final class PartsResolver implements ValueResolverInterface
{
    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(ValueResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = []
    ): ResolvedValueWithFixtureSet
    {
        if (is_array($value)) {
            return $this->resolver->resolve($value, $fixture, $fixtureSet, $scope);
        }

        if (is_string($value) === false) {
            return new ResolvedValueWithFixtureSet($value, $fixtureSet);
        }

        if (1 === preg_match('/^(?:\d+(?:\.\d*)?)|(?:<.*>)x .*/', $value)) {
            return $this->resolver->resolve($value, $fixture, $fixtureSet, $scope);
        }

        if (1 === preg_match('/^(?:(.*?)?(?:(<.*>)|(\[.*\])))+(.*?)?$/', $value, $matches)) {
            unset($matches[0]);
            foreach ($matches as $index => $match) {
                if ($match === '' || trim($match) === '') {
                    continue;
                }
                $result = $this->resolver->resolve($match, $fixture, $fixtureSet, $scope);

                $fixtureSet = $result->getSet();
                $matches[$index] = $result->getValue();
            }

            $value = implode('', $matches);
        }

        return new ResolvedValueWithFixtureSet($value, $fixtureSet);
    }
}
