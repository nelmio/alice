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

use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__.'/../../vendor-bin/profiling/vendor/autoload.php';

$blackfire = new \Blackfire\Client();

$config = new \Blackfire\Profile\Configuration();
$config->setTitle('Scenario 3');
$config->setSamples(1);
$config->setReference(6);

$probe = $blackfire->createProbe($config, false);

$output = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$progressBar = new ProgressBar($output, $config->getSamples());

$output->writeln(
    sprintf(
        'Start profiling of <info>%s</info> with <info>%d samples.</info>',
        $config->getTitle(),
        $config->getSamples()
    )
);

$loader = new NativeLoader();
for ($i = 1; $i <= $config->getSamples(); $i++) {
    $probe->enable();

    $loader->loadFile(__DIR__.'/fixtures.yml');

    $probe->close();
    $progressBar->advance();
}

$blackfire->endProbe($probe);

$output->success('Finished!');
