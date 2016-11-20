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

namespace Nelmio\Alice;

use Faker\Factory;
use Faker\Generator;
use Nelmio\Alice\scenario2\ImmutableUser;
use Nelmio\Alice\scenario2\MutableUser;
use Nelmio\Alice\scenario2\PublicUser;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__.'/../../vendor-bin/profiling/vendor/autoload.php';

$blackfire = new \Blackfire\Client();

$config = new \Blackfire\Profile\Configuration();
$config->setTitle('Scenario 0');
$config->setSamples(10);
$config->setReference(0);

$probe = $blackfire->createProbe($config, false);

$output = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$progressBar = new ProgressBar($output, $config->getSamples());

$faker = Factory::create();

$output->writeln(
    sprintf(
        'Start profiling of <info>%s</info> with <info>%d samples.</info>',
        $config->getTitle(),
        $config->getSamples()
    )
);

for ($i = 1; $i <= $config->getSamples(); $i++) {
    $probe->enable();

    script($faker);

    $probe->close();
    $progressBar->advance();
}

$blackfire->endProbe($probe);

$output->success('Finished!');

function script(Generator $faker)
{
    $objects = [];

    for ($i = 0; $i <= 1000; $i++) {
        $objects['immutable_user_'.$i] = new ImmutableUser(
            $faker->userName,
            $faker->name,
            $faker->dateTime,
            $faker->email,
            $faker->randomNumber
        );
    }

    for ($i = 0; $i <= 1000; $i++) {
        $user = new MutableUser();
        $user->setUsername($faker->userName);
        $user->setFullname($faker->name);
        $user->setBirthDate($faker->dateTime);
        $user->setEmail($faker->email);
        $user->setFavoriteNumber($faker->randomNumber);

        $objects['mutable_user_'.$i] = $user;
    }

    for ($i = 0; $i <= 1000; $i++) {
        $user = new PublicUser();
        $user->username = $faker->userName;
        $user->fullname = $faker->name;
        $user->birthDate = $faker->dateTime;
        $user->email = $faker->email;
        $user->favoriteNumber = $faker->randomNumber;

        $objects['public_user_'.$i] = $user;
    }

    for ($i = 0; $i <= 1000; $i++) {
        $user = new \stdClass();
        $user->username = $faker->userName;
        $user->fullname = $faker->name;
        $user->birthDate = $faker->dateTime;
        $user->email = $faker->email;
        $user->favoriteNumber = $faker->randomNumber;

        $objects['public_user_'.$i] = $user;
    }

    return $objects;
}
