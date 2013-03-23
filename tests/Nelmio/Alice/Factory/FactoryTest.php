<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Factory;

use Nelmio\Alice\Factory\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    public function setUp()
    {
        $this->factory = new Factory();
    }

    public function defineSimpleEntity($name = 'user')
    {
        $this->factory
            ->define($name)
            ->of('Entity\User')
            ->values(array('username' => 'Bob', 'email' => '<email()>'))
        ;

        return $this->factory;
    }

    public function testBuildSimpleEntity()
    {
        $data = $this->defineSimpleEntity()->build('user');

        $expected = array(
            'Entity\User' => array(
                'user' => array(
                    'username' => 'Bob',
                    'email' => '<email()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }

    public function testBuildDatasets()
    {
        $this->defineSimpleEntity('user1');
        $this->defineSimpleEntity('user2');

        $data = $this->factory->with('user1')->build('user2');

        $expected = array(
            'Entity\User' => array(
                'user1' => array(
                    'username' => 'Bob',
                    'email' => '<email()>'
                ),
                'user2' => array(
                    'username' => 'Bob',
                    'email' => '<email()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }

    public function testImportDefinition()
    {
        $this->factory
            ->import(__DIR__ . '/../fixtures/complete.yml')
        ;

        $expected = array(
            'Nelmio\Alice\fixtures\User' => array(
                'user0' => array(
                    'username' => 'johnny',
                    'fullname' => 'John Smith',
                    'birthDate' => '339980400',
                    'email' => '<email()>',
                    'favoriteNumber' => 42
                )
            )
        );

        $this->assertEquals($expected, $this->factory->build('user0'));
    }

    public function testBuildMultipleSimpleEntity()
    {
        $data = $this->defineSimpleEntity()->build('user', 10);

        $expected = array(
            'Entity\User' => array(
                'user{1..10}' => array(
                    'username' => 'Bob',
                    'email' => '<email()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }

    public function testBuildSimpleEntityWithOverridenValues()
    {
        $data = $this->defineSimpleEntity()->build('user', 1, array('username' => 'Alice'));

        $expected = array(
            'Entity\User' => array(
                'user' => array(
                    'username' => 'Alice',
                    'email' => '<email()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }

    public function testBuildSimpleEntityWithInheritance()
    {
        $this->defineSimpleEntity();
        $this->factory->define('superhero < user')->values(array('username' => 'Superhero'));

        $data = $this->factory->build('superhero');

        $expected = array(
            'Entity\User' => array(
                'superhero' => array(
                    'username' => 'Superhero',
                    'email' => '<email()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }

    public function testBuildComplexEntityWithAssocations()
    {
        $this->defineSimpleEntity();
        $this->factory
            ->define('tag')
            ->of('Entity\Tag')
            ->values(array('name' => '<word()>'))
        ;

        $this->factory
            ->define('article')
            ->of('Entity\Article')
            ->constructWith('Alice and Bob')
            ->assocOne('author', 'user')
            ->assocMany('tags', 'tag*', 5)
            ->values(array('content' => '<paragraph()>'))
        ;

        $data = $this->factory->build('article');

        $expected = array(
            'Entity\User' => array(
                'user' => array(
                    'username' => 'Bob',
                    'email' => '<email()>'
                )
            ),
            'Entity\Tag' => array(
                'tag{1..5}' => array(
                    'name' => '<word()>'
                )
            ),
            'Entity\Article' => array(
                'article' => array(
                    '__construct' => array('Alice and Bob'),
                    'author' => '@user',
                    'tags' => '5x @tag*',
                    'content' => '<paragraph()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }

    public function testBuildComplexEntityWithManualAssocations()
    {
        $this->defineSimpleEntity();
        $this->factory
            ->define('tag')
            ->of('Entity\Tag')
            ->values(array('name' => '<word()>'))
            ->end()

            ->define('tag_alice < tag')
            ->values(array('name' => 'Alice'))
            ->end()

            ->define('tag_bob < tag')
            ->values(array('name' => 'Bob'))
            ->end()
        ;

        $this->factory
            ->define('article')
            ->of('Entity\Article')
            ->constructWith('Alice and Bob')
            ->assocOne('author', 'user')
            ->assocMany('tags', array('tag_alice', 'tag_bob'))
            ->values(array('content' => '<paragraph()>'))
        ;

        $data = $this->factory->build('article');

        $expected = array(
            'Entity\User' => array(
                'user' => array(
                    'username' => 'Bob',
                    'email' => '<email()>'
                )
            ),
            'Entity\Tag' => array(
                'tag_alice' => array(
                    'name' => 'Alice'
                ),
                'tag_bob' => array(
                    'name' => 'Bob'
                )
            ),
            'Entity\Article' => array(
                'article' => array(
                    '__construct' => array('Alice and Bob'),
                    'author' => '@user',
                    'tags' => '[@tag_alice, @tag_bob]',
                    'content' => '<paragraph()>'
                )
            )
        );

        $this->assertEquals($expected, $data);
    }
}
