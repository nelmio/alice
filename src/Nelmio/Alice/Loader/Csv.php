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

use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Loads fixtures from a yaml file
 *
 * The yaml file can contain PHP which will be executed before it is parsed as yaml.
 * PHP in the yaml file has access to $loader->fake() to generate data
 *
 * The general format of the file must follow this example:
 *
 *     Namespace\Class:
 *         name:
 *             property: value
 *             property2: value
 *         name2:
 *             [...]
 */
class Csv extends Base
{
    /**
     * {@inheritDoc}
     */
    public function load($file, $entity)
    {
        $data = array (
            'Lion\\Bundle\\XXXXSponsorshipBundle\\Entity\\Game' =>
                array (
                    'gameWinner' =>
                        array (
                            'gameCode' => 'gameWinner',
                            'group' => 'Bob',
                            'name' => 'Craig vs Jeremy',
                            'start' => '<dateTimeBetween(\'-1 week\', \'now\')>',
                            'end' => '<dateTimeBetween(\'now\', \'+1 week\')>',
                            'mascot' => '@mascot1',
                        ),
                    'gameLoser' =>
                        array (
                            'gameCode' => 'gameLoser',
                            'group' => 'Bob',
                            'name' => 'Craig vs Jeremy',
                            'start' => '<dateTimeBetween(\'-1 week\', \'now\')>',
                            'end' => '<dateTimeBetween(\'now\', \'+1 week\')>',
                            'mascot' => '@mascot2',
                        ),
                    'gameNoStart' =>
                        array (
                            'gameCode' => 'gameNoStart',
                            'group' => 'Bob',
                            'name' => 'Craig vs Jeremy',
                            'start' => '<dateTimeBetween(\'now\', \'+200 days\')>',
                            'end' => '<dateTimeBetween($start, date(\'Y-m-d\', strtotime(\'+1 month\', $start->getTimestamp())))>',
                        ),
                    'gameHasEnded' =>
                        array (
                            'gameCode' => 'gameHasEnded',
                            'group' => 'Bob',
                            'name' => 'Craig vs Jeremy',
                            'end' => '<dateTimeBetween(\'-200 days\', \'now\')>',
                            'start' => '<dateTimeBetween(date(\'Y-m-d\', strtotime(\'-1 month\', $end->getTimestamp())), $end)>',
                        ),
                    'gameNoMascot' =>
                        array (
                            'gameCode' => 'gameNoMascot',
                            'group' => 'Bob',
                            'name' => 'Craig vs Jeremy',
                            'start' => '<dateTimeBetween(\'-1 week\', \'now\')>',
                            'end' => '<dateTimeBetween(\'now\', \'+1 week\')>',
                        ),
                ),
            'Lion\\Bundle\\XXXXSponsorshipBundle\\Entity\\Code' =>
                array (
                    'codeWinner{1..70}' =>
                        array (
                            'code' => 'winner<current()>',
                            'game' => '@gameWinner',
                            'mascot' => '@mascot1',
                        ),
                    'codeLoser{1..10}' =>
                        array (
                            'code' => 'loser<current()>',
                            'game' => '@gameLoser',
                            'mascot' => '@mascot1',
                        ),
                    'codeUsed{1..10}' =>
                        array (
                            'code' => 'used<current()>',
                            'game' => '@game*',
                            'mascot' => '@mascot*',
                        ),
                    'codeNoStart{1..10}' =>
                        array (
                            'code' => 'gameNoStart<current()>',
                            'game' => '@gameNoStart',
                            'mascot' => '@mascot*',
                        ),
                    'codeHasEnded{1..10}' =>
                        array (
                            'code' => 'gameHasEnded<current()>',
                            'game' => '@gameHasEnded',
                            'mascot' => '@mascot*',
                        ),
                    'codeNoMascotYet{1..10}' =>
                        array (
                            'code' => 'noMascot<current()>',
                            'game' => '@gameNoMascot',
                            'mascot' => '@mascot*',
                        ),
                ),
            'Lion\\Bundle\\XXXXSponsorshipBundle\\Entity\\Entry' =>
                array (
                    'entryUsed' =>
                        array (
                            'code' => '@codeUsed*',
                            'prize' => '@prizeSoldOut',
                            'first_name' => '<firstName()>',
                            'surname' => '<lastName()>',
                            'dob' => '<date()>',
                            'mobile' => '@mobile*',
                            'email' => '<email()>',
                            'state' => '<state()>',
                            'postcode' => '<postcode()>',
                            'fav_team' => '<randomElement(array(\'Broncos\',\'Bulldogs\',\'Cowboys\',\'Dragons\',\'Eels\',\'Knights\',\'Panthers\',\'Rabbitohs\',\'Raiders\',\'Roosters\',\'Sea Eagles\',\'Sharks\',\'Storm\',\'Tigers\',\'Titans\',\'Warriors\'))>',
                            'terms' => '<boolean(100)>',
                            'optin' => '<boolean(50)>',
                            'street' => '<streetAddress()>',
                            'suburb' => '<city()>',
                            'completed' => true,
                        ),
                    'entryWithinTenMinutes' =>
                        array (
                            'code' => '@codeUsed*',
                            'prize' => '@prizeTemporarySoldOut',
                            'first_name' => '<firstName()>',
                            'surname' => '<lastName()>',
                            'dob' => '<date()>',
                            'mobile' => '@mobile*',
                            'email' => '<email()>',
                            'state' => '<state()>',
                            'postcode' => '<postcode()>',
                            'fav_team' => '<randomElement(array(\'Broncos\',\'Bulldogs\',\'Cowboys\',\'Dragons\',\'Eels\',\'Knights\',\'Panthers\',\'Rabbitohs\',\'Raiders\',\'Roosters\',\'Sea Eagles\',\'Sharks\',\'Storm\',\'Tigers\',\'Titans\',\'Warriors\'))>',
                            'terms' => '<boolean(100)>',
                            'optin' => '<boolean(50)>',
                            'street' => '<streetAddress()>',
                            'suburb' => '<city()>',
                            'completed' => false,
                            'created' => '<dateTimeBetween(\'-10 minutes\', \'now\')>',
                        ),
                ),
            'Lion\\Bundle\\XXXXSponsorshipBundle\\Entity\\Prize' =>
                array (
                    'prizeAvailable' =>
                        array (
                            'name' => 'I\'m Available!',
                            'qty' => 100,
                            'image' => '<imageUrl(262, 152, \'technics\')>',
                            'rrp' => '<randomFloat(2, 0, 100)>',
                            'visible' => 1,
                        ),
                    'prizeSoldOut' =>
                        array (
                            'name' => 'I\'m Sold Out!',
                            'qty' => 1,
                            'image' => '<imageUrl(262, 152, \'sports\')>',
                            'rrp' => '<randomFloat(2, 0, 100)>',
                            'visible' => 1,
                        ),
                    'prizeTemporarySoldOut' =>
                        array (
                            'name' => 'I\'m sold out for the next 10 minute!',
                            'qty' => 1,
                            'image' => '<imageUrl(262, 152, \'cats\')>',
                            'rrp' => '<randomFloat(2, 0, 100)>',
                            'visible' => 1,
                        ),
                ),
        );

        return parent::load($data);
    }
}
