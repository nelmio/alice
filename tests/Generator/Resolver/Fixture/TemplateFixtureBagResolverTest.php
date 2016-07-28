<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\Fixture\FixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBag;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver
 * @covers Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureResolver
 */
class TemplateFixtureBagResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateFixtureBagResolver
     */
    private $resolver;

    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $this->propRefl = (new \ReflectionClass(TemplatingFixture::class))->getProperty('fixture');
        $this->propRefl->setAccessible(true);

        $this->resolver = new TemplateFixtureBagResolver();
    }

    public function testResolvesTemplatesFixtures()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                $group1 = new SimpleFixture(
                    'group1',
                    'Nelmio\Entity\Group',
                    new SpecificationBag(
                        null,
                        new PropertyBag(),
                        new MethodCallBag()
                    )
                )
            )
            ->with(
                $group2 = new FixtureWithFlags(
                    new SimpleFixture(
                        'group2',
                        'Nelmio\Entity\Group',
                        new SpecificationBag(
                            null,
                            new PropertyBag(),
                            new MethodCallBag()
                        )
                    ),
                    (new FlagBag('group2'))
                        ->with(new ElementFlag('dummy_flag'))
                )
            )
            ->with(
                $user1 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user1'))
                            ->with(new ExtendFlag(new FixtureReference('user2')))
                            ->with(new ExtendFlag(new FixtureReference('user3')))
                            ->with(new ElementFlag('dummy_flag'))
                    )
                )
            )
            ->with(
                $user2 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user2',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v21'))
                                    ->with(new Property('p2', 'v22'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user2'))
                            ->with(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user3 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user3',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v31'))
                                    ->with(new Property('p2', 'v32'))
                                    ->with(new Property('p3', 'v33'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user3'))
                            ->with(new ExtendFlag(new FixtureReference('user4')))
                    )
                )
            )
            ->with(
                $user4 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user4',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v41'))
                                    ->with(new Property('p2', 'v42'))
                                    ->with(new Property('p3', 'v43'))
                                    ->with(new Property('p4', 'v44'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user4'))
                            ->with(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user5 = new FixtureWithFlags(  // has a template flag but is not a templating fixture!
                    new SimpleFixture(
                        'user5',
                        'Nelmio\Entity\User',
                        new SpecificationBag(
                            null,
                            new PropertyBag(),
                            new MethodCallBag()
                        )
                    ),
                    (new FlagBag('user5'))
                        ->with(new TemplateFlag())
                )
            )
        ;
        $expected = (new FixtureBag())
            ->with($group1)
            ->with($group2)
            ->with(
                new FixtureWithFlags(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                                    ->with(new Property('p2', 'v22'))
                                    ->with(new Property('p3', 'v33'))
                                    ->with(new Property('p4', 'v44'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        $this->getDecoratedFixturesFlag($user1)
                    ),
                    (new FlagBag('user1'))
                        ->with(new ElementFlag('dummy_flag'))
                )
            )
            ->with(
                new FixtureWithFlags(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user3',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v31'))
                                    ->with(new Property('p2', 'v32'))
                                    ->with(new Property('p3', 'v33'))
                                    ->with(new Property('p4', 'v44'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        $this->getDecoratedFixturesFlag($user3)
                    ),
                    new FlagBag('user3')
                )
            )
            ->with($user5)
        ;

        $actual = $this->resolver->resolve($unresolvedFixtures);
        $this->assertEquals($expected, $actual);
    }

    public function testTheOrderOfFixturesGivesTheSameResult()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                $user4 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user4',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v41'))
                                    ->with(new Property('p2', 'v42'))
                                    ->with(new Property('p3', 'v43'))
                                    ->with(new Property('p4', 'v44'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user4'))
                            ->with(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user3 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user3',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v31'))
                                    ->with(new Property('p2', 'v32'))
                                    ->with(new Property('p3', 'v33'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user3'))
                            ->with(new ExtendFlag(new FixtureReference('user4')))
                    )
                )
            )
            ->with(
                $user2 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user2',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v21'))
                                    ->with(new Property('p2', 'v22'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user2'))
                            ->with(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user1 = new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user1'))
                            ->with(new ExtendFlag(new FixtureReference('user2')))
                            ->with(new ExtendFlag(new FixtureReference('user3')))
                            ->with(new ElementFlag('dummy_flag'))
                    )
                )
            )
        ;
        $expected = (new FixtureBag())
            ->with(
                new FixtureWithFlags(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                                    ->with(new Property('p2', 'v22'))
                                    ->with(new Property('p3', 'v33'))
                                    ->with(new Property('p4', 'v44'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        $this->getDecoratedFixturesFlag($user1)
                    ),
                    (new FlagBag('user1'))
                        ->with(new ElementFlag('dummy_flag'))
                )
            )
            ->with(
                new FixtureWithFlags(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user3',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v31'))
                                    ->with(new Property('p2', 'v32'))
                                    ->with(new Property('p3', 'v33'))
                                    ->with(new Property('p4', 'v44'))
                                ,
                                new MethodCallBag()
                            )
                        ),
                        $this->getDecoratedFixturesFlag($user3)
                    ),
                    new FlagBag('user3')
                )
            )
        ;

        $actual = $this->resolver->resolve($unresolvedFixtures);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureNotFoundException
     * @expectedExceptionMessage Could not find the fixture "user_base".
     */
    public function testThrowExceptionIfFixtureNotFound()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new FixtureWithFlags(
                        new SimpleFixture(
                            'user0',
                            'Nelmio\Entity\User',
                            new SpecificationBag(
                                null,
                                new PropertyBag(),
                                new MethodCallBag()
                            )
                        ),
                        (new FlagBag('user0'))
                            ->with(
                                new ExtendFlag(
                                    new FixtureReference('user_base')
                                )
                            )
                    )
                )
            )
        ;
        $this->resolver->resolve($unresolvedFixtures);
    }

    private function getDecoratedFixturesFlag(TemplatingFixture $fixture)
    {
        return $this->propRefl->getValue($fixture)->getFlags();
    }
}
