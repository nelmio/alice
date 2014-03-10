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

    public function testParseOneLevelExtend()
    {
        $file = __DIR__ . '/../fixtures/inheritance.yml';
        $loader = new \Nelmio\Alice\Loader\Yaml();
        $data = $loader->parse($file);
        $expectedData = array(
            'Nelmio\Alice\fixtures\Product' => array(
                'product0' => array(
                    'status'    => 'in_stock',
                    'site'      => '<word()>',
                    'changed'   => 'y',
                    'locked'    => '<word()>',
                    'cancelled' => '<word()>',
                    'canBuy'    => 'y',
                    'package'   => 'n',
                    'price'     => '<randomFloat()>',
                    'amount'    => 1,
                    'markDeleted'=> '<word()>',
                    'paid'      => 'y',
                    'user'      => '@user1',
                ),
                'product1' => array(
                    'status'    => 'in_stock',
                    'site'      => '<word()>',
                    'changed'   => 'n',
                    'locked'    => '<word()>',
                    'cancelled' => '<word()>',
                    'canBuy'    => 'y',
                    'package'   => 'n',
                    'price'     => '<randomFloat()>',
                    'amount'    => 45,
                    'markDeleted'=> '<word()>',
                    'paid'      => 'n',
                    'user'      => '@user0',
                ),

            )
        );
        $this->assertEquals($expectedData, $data);
    }

}