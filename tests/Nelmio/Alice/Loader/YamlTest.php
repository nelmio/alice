<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

class YamlTest extends \PHPUnit_Framework_TestCase
{
    public function testIncludeFiles()
    {
        $file = __DIR__ . '/../fixtures/include.yml';
        $loader = new \Nelmio\Alice\Loader\Yaml();
        $reflMethod = new \ReflectionMethod($loader, 'parse');
        $reflMethod->setAccessible(true);
        $data = $reflMethod->invoke($loader, $file);
        $expectedData = array(
            'Nelmio\\Alice\\fixtures\\Product' =>
                array(
                    'product_base (template)' =>
                        array(
                            'status' => 'in_stock',
                            'site' => '<word()>',
                            'changed' => 'n',
                            'locked' => '<word()>',
                            'cancelled' => '<word()>',
                            'canBuy' => 'y',
                            'package' => 'n',
                            'price' => '<randomFloat()>',
                            'amount' => 1,
                            'markDeleted' => '<word()>',
                            'paid' => 'y',
                        ),
                    'product1' =>
                        array(
                            'amount' => 45,
                            'paid' => 'n',
                            'user' => '@user0',
                        ),
                    'product0' =>
                        array(
                            'changed' => 'y',
                            'user' => '@user1',
                        ),
                ),
            'Nelmio\\Alice\\fixtures\\Shop' =>
                array(
                    'shop2' =>
                        array(
                            'domain' => 'amazon.com',
                        ),
                    'shop1' =>
                        array(
                            'domain' => 'ebay.com',
                        ),
                ),
            'Nelmio\\Alice\\fixtures\\User' =>
                array(
                    'user_base (template)' =>
                        array(
                            'email' => '<email()>',
                        ),
                ),
        );
        $this->assertEquals($expectedData, $data);
    }

}