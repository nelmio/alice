<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Nelmio\Alice\Fixtures\Fixture;

$data = [
    'standard' => [],
];

$invalidCharacters = [
    '‘' => 'left single quote',
    '’' => 'right single quote',
    '‚' => 'single low-9 quote',
    '“' => 'left double quote',
    '”' => 'right double quote',
    '„' => 'double low-9 quote',
    '†' => 'dagger',
    '‡' => 'double dagger',
    '‰' => 'per mill sign',
    '‹' => 'single left-pointing angle quote',
    '›' => 'single right-pointing angle quote',
    '♠' => 'black spade suit',
    '♣' => 'black club suit',
    '♥' => 'black heart suit',
    '♦' => 'black diamond suit',
    '‾' => 'overline, = spacing overscore',
    '←' => 'leftward arrow',
    '↑' => 'upward arrow',
    '→' => 'rightward arrow',
    '↓' => 'downward arrow',
    '™' => 'trademark sign',
    '!' => 'exclamation mark',
    '#' => 'number sign',
    '$' => 'dollar sign',
    '%' => 'percent sign',
    '&' => 'ampersand',
    '(' => 'left parenthesis',
    ')' => 'right parenthesis',
    '*' => 'asterisk',
    '+' => 'plus sign',
    ',' => 'comma',
    '-' => 'hyphen',
    ':' => 'colon',
    's' => 'emicolon',
    '<' => 'less-than sign',
    '=' => 'equals sign',
    '>' => 'greater-than sign',
    '?' => 'question mark',
    '@' => 'at sign',
    'u' => 'ppercase letters A-Z',
    '[' => 'left square bracket',
    '\\' => 'backslash',
    ']' => 'caret',
    '_' => 'horizontal bar (underscore)',
    '`' => 'grave accent',
    '{' => 'left curly brace',
    '|' => 'vertical bar',
    '}' => 'right curly brace',
    '~' => 'tilde',
    '–' => 'en dash',
    '—' => 'em dash',
    'n' => 'onbreaking space',
    '¡' => 'inverted exclamation',
    '¢' => 'cent sign',
    '£' => 'pound sterling',
    '¤' => 'general currency sign',
    '¥' => 'yen sign',
    '¦' => 'broken vertical bar',
    '§' => 'section sign',
    '¨' => 'umlaut',
    '©' => 'copyright',
    'ª' => 'feminine ordinal',
    '«' => 'left angle quote',
    '¬' => 'not sign',
    '®' => 'registered trademark',
    '¯' => 'macron accent',
    '°' => 'degree sign',
    '±' => 'plus or minus',
    '²' => 'superscript two',
    '³' => 'superscript three',
    '´' => 'acute accent',
    'µ' => 'micro sign',
    '¶' => 'paragraph sign',
    '·' => 'middle dot',
    '¸' => 'cedilla',
    '¹' => 'superscript one',
    'º' => 'masculine ordinal',
    '»' => 'right angle quote',
    '¼' => 'one-fourth',
    '½' => 'one-half',
    '¾' => 'three-fourths',
    '¿' => 'inverted question mark',
    'À' => 'uppercase A, grave accent',
];

$data['standard']['simple'] = [
    'user',
    [
        ['user', ''],
    ]
];
$data['standard']['simple with upper case'] = [
    'UseR',
    [
        ['UserR', ''],
    ]
];
$data['standard']['simple with special allowed characters'] = [
    'user.or_something/else',
    [
        ['user100.or_something/else', ''],
    ]
];
foreach ($invalidCharacters as $invalidCharacter) {
    foreach ($data['standard'] as $standardTitle => $data) {
        $name = $data['standard'][$standardTitle][0];

        $data['standard']["{$standardTitle} | starts with invalid character: \"{$invalidCharacter}\""] =
        [
            $invalidCharacter.$name,
            null,
        ];

        $data['standard']["{$standardTitle} | ends with invalid character: \"{$invalidCharacter}\""] =
        [
            $name.$invalidCharacter,
            null,
        ];

        if (strlen($name) > 2) {
            $midLength = (int) (strlen($name)/2);
            $data['standard']["{$standardTitle} | has invalid character: \"{$invalidCharacter}\""] =
            [
                substr($name, 0, $midLength).$invalidCharacter.substr($name, $midLength),
                null,
            ];
        }
    }
}

$data['standard']['shortest'] = [
    'u',
    [
        ['u', ''],
    ]
];
$data['standard']['empty'] = [
    '',
    null,
];

$allowedSpecialCharacters = [
    '.',
    '_',
    '/',
    '0',
    '1',
];
foreach ($allowedSpecialCharacters as $allowedSpecialCharacter) {
    $data['standard']["shortest with allowed special character \"{$allowedSpecialCharacter}\""] = [
        $allowedSpecialCharacter,
        null,
    ];
}

return $data;

function generateData($name, array $fixtures)
{
    $class = 'Dummy';
    $specs = [];

    $return = [
        $class,
        $name,
        $specs,
        []
    ];

    foreach ($fixtures as $fixture) {
        $return[3][] = new Fixture(
            $class,
            $fixture[0],
            $specs,
            $fixture[1]
        );
    }

    return $return;
}
