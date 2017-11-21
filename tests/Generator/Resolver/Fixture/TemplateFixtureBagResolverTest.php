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

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver
 * @covers \Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureResolver
 */
class TemplateFixtureBagResolverTest extends TestCase
{
    /**
     * @var TemplateFixtureBagResolver
     */
    private $resolver;

    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->propRefl = (new ReflectionClass(TemplatingFixture::class))->getProperty('fixture');
        $this->propRefl->setAccessible(true);

        $this->resolver = new TemplateFixtureBagResolver();
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(TemplateFixtureBagResolver::class))->isCloneable());
    }

    public function testResolvesTemplatesFixturesAndReturnsResultingFixtureBag()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                $group1 = new SimpleFixture(
                    'group1',
                    'Nelmio\Entity\Group',
                    SpecificationBagFactory::create()
                )
            )
            ->with(
                $group2 = new SimpleFixtureWithFlags(
                    new SimpleFixture(
                        'group2',
                        'Nelmio\Entity\Group',
                        SpecificationBagFactory::create()
                    ),
                    (new FlagBag('group2'))
                        ->withFlag(new ElementFlag('dummy_flag'))
                )
            )
            ->with(
                $user1 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                            )
                        ),
                        (new FlagBag('user1'))
                            ->withFlag(new ExtendFlag(new FixtureReference('user3')))
                            ->withFlag(new ExtendFlag(new FixtureReference('user2')))
                            ->withFlag(new ElementFlag('dummy_flag'))
                    )
                )
            )
            ->with(
                $user2 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user2',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v21'))
                                    ->with(new Property('p2', 'v22'))
                            )
                        ),
                        (new FlagBag('user2'))
                            ->withFlag(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user3 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user3',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v31'))
                                    ->with(new Property('p2', 'v32'))
                                    ->with(new Property('p3', 'v33'))
                            )
                        ),
                        (new FlagBag('user3'))
                            ->withFlag(new ExtendFlag(new FixtureReference('user4')))
                            ->withFlag(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user4 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user4',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v41'))
                                    ->with(new Property('p2', 'v42'))
                                    ->with(new Property('p3', 'v43'))
                                    ->with(new Property('p4', 'v44'))
                            )
                        ),
                        (new FlagBag('user4'))
                            ->withFlag(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user5 = new SimpleFixtureWithFlags(// has a template flag but is not a templating fixture!
                    new SimpleFixture(
                        'user5',
                        'Nelmio\Alice\Entity\User',
                        SpecificationBagFactory::create()
                    ),
                    (new FlagBag('user5'))
                        ->withFlag(new TemplateFlag())
                )
            )
        ;
        $expected = (new FixtureBag())
            ->with($group1)
            ->with($group2)
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                                    ->with(new Property('p2', 'v22'))
                                    ->with(new Property('p3', 'v33'))
                                    ->with(new Property('p4', 'v44'))
                            )
                        ),
                        $this->getDecoratedFixturesFlag($user1)
                    ),
                    (new FlagBag('user1'))
                        ->withFlag(new ElementFlag('dummy_flag'))
                )
            )
            ->with($user5)
        ;

        $actual = $this->resolver->resolve($unresolvedFixtures);
        $this->assertEquals($expected, $actual);
    }

    public function testTheResolutionIsInvarientToTheOrderInWhichFixturesAreGiven()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                $user4 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user4',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v41'))
                                    ->with(new Property('p2', 'v42'))
                                    ->with(new Property('p3', 'v43'))
                                    ->with(new Property('p4', 'v44'))
                            )
                        ),
                        (new FlagBag('user4'))
                            ->withFlag(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user3 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user3',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v31'))
                                    ->with(new Property('p2', 'v32'))
                                    ->with(new Property('p3', 'v33'))
                            )
                        ),
                        (new FlagBag('user3'))
                            ->withFlag(new ExtendFlag(new FixtureReference('user4')))
                            ->withFlag(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user2 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user2',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v21'))
                                    ->with(new Property('p2', 'v22'))
                            )
                        ),
                        (new FlagBag('user2'))
                            ->withFlag(new TemplateFlag())
                    )
                )
            )
            ->with(
                $user1 = new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                            )
                        ),
                        (new FlagBag('user1'))
                            ->withFlag(new ExtendFlag(new FixtureReference('user3')))
                            ->withFlag(new ExtendFlag(new FixtureReference('user2')))
                            ->withFlag(new ElementFlag('dummy_flag'))
                    )
                )
            )
        ;
        $expected = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user1',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create(
                                null,
                                (new PropertyBag())
                                    ->with(new Property('p1', 'v11'))
                                    ->with(new Property('p2', 'v22'))
                                    ->with(new Property('p3', 'v33'))
                                    ->with(new Property('p4', 'v44'))
                            )
                        ),
                        $this->getDecoratedFixturesFlag($user1)
                    ),
                    (new FlagBag('user1'))
                )
            )
        ;

        $actual = $this->resolver->resolve($unresolvedFixtures);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureNotFoundException
     * @expectedExceptionMessage Could not find the fixture "user_base".
     */
    public function testThrowsAnExceptionIfFixtureExtendsANonExistingFixture()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user0',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create()
                        ),
                        (new FlagBag('user0'))
                            ->withFlag(
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Fixture "user0" extends "user_base" but "user_base" is not a template.
     */
    public function testThrowsAnExceptionIfAFixtureExtendANonTemplateFixture()
    {
        $unresolvedFixtures = (new FixtureBag())
            ->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            'user0',
                            'Nelmio\Alice\Entity\User',
                            SpecificationBagFactory::create()
                        ),
                        (new FlagBag('user0'))
                            ->withFlag(
                                new ExtendFlag(
                                    new FixtureReference('user_base')
                                )
                            )
                    )
                )
            )
            ->with(
                new SimpleFixture(
                    'user_base',
                    'Nelmio\Alice\Entity\User',
                    SpecificationBagFactory::create()
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
