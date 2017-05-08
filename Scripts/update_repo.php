#!/usr/bin/env php
<?php

$reposToPull = array(
    '~/.bin/phpcs' => 'origin 2.9',
    '~/.bin/phpcs/src/Standards/TFD' => 'origin master',
);

echo('Updating repositories..' . PHP_EOL);

foreach ($reposToPull as $repo => $target) {

    echo(' - ' . $repo . ($target ? ' @ ' . $target : '') . PHP_EOL);

    exec('git --git-dir ' . $repo . '/.git --work-tree ' . $repo . '/ pull ' . $target, $output, $exitCode);
    if ($exitCode > 0) {
        echo('Update failed with exit code ' . $exitCode . PHP_EOL);
        exit($exitCode);
    }

}

echo('Done.' . PHP_EOL);
