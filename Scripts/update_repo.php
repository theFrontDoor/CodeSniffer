#!/usr/bin/env php
<?php

$reposToPull = array(
    '/usr/local/phpcs' => 'origin 2.9',
    '/usr/local/phpcs/CodeSniffer/Standards/TFD' => 'origin master',
);

echo('Updating repositories..' . PHP_EOL);

foreach ($reposToPull as $repo => $target) {

    echo(' - ' . $repo . ($target ? ' @ ' . $target : '') . PHP_EOL);

    exec('git -C ' . $repo . '/ pull ' . $target, $output, $exitCode);
    if ($exitCode > 0) {
        echo('Update failed with exit code ' . $exitCode . PHP_EOL);
        exit($exitCode);
    }

}

echo('Done.' . PHP_EOL);
