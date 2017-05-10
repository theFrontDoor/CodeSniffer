#!/usr/bin/env php
<?php

$dirArg = './';
if (isset($argv[1])) {


    $dirArg = $argv[1];
    if (substr($dirArg, -1) !== DIRECTORY_SEPARATOR) {
        $dirArg .= DIRECTORY_SEPARATOR;
    }

}

$cwd = getcwd();
chdir($dirArg);

echo('Collecting files..' . PHP_EOL);
$files = array_filter(explode(PHP_EOL, trim(shell_exec($cmd = 'git diff-tree --no-commit-id --name-only -r HEAD'))), function($value) {
    return $value !== '';
});

chdir($cwd);

echo($cmd . ' ' . PHP_EOL);

if (empty($files)) {
    echo('  - No changed files found, assuming merge request, exiting' . PHP_EOL);
    exit(0);
}

$cmdStr = 'phpcs -d memory_limit=-1 --standard=TFD --report=json --extensions=inc,php ';
$gotFile = FALSE;

foreach ($files as $file) {

    $fullFile = $dirArg . $file;
    if (!file_exists($fullFile)) { // File got removed in git, nothing to sniff
        echo('  - ' . $file . ' doesn\'t exist anymore, assuming removal and skipping' . PHP_EOL);
        continue;
    }

    if (substr($fullFile, -4) !== '.php' && substr($fullFile, -8) !== '.php.inc') {
        echo(' - ' . $file . ' skipping due to invalid file extension' . PHP_EOL);
        continue;
    }

    if (is_dir($fullFile)) {
        echo('  - ' . $file . ' is a directory, assuming submodule and skipping' . PHP_EOL);
        continue;
    }

    $gotFile = TRUE;

    echo('  - ' . $file . PHP_EOL);
    $cmdStr .= escapeshellarg($fullFile) . ' ';

}

if (!$gotFile) {
    echo(PHP_EOL . 'Didn\'t find any files to lint, exiting..' . PHP_EOL . PHP_EOL);
    exit(0);
}

echo(PHP_EOL . 'Executing: ' . $cmdStr . PHP_EOL);
$rawJSON = shell_exec($cmdStr);
$result = json_decode($rawJSON, TRUE);
if (!$result) {
    echo('Unable to decode json:' . PHP_EOL);
    echo($rawJSON . PHP_EOL);
    exit(1);
}

echo(PHP_EOL . 'Total: ' . PHP_EOL . '  ' . $result['totals']['errors'] . ' errors' . PHP_EOL . '  ' . $result['totals']['warnings'] . ' warnings' . PHP_EOL . '  ' . $result['totals']['fixable'] . ' fixable' . PHP_EOL . PHP_EOL);

$buildDir = getenv('CI_PROJECT_DIR');
$buildDirLen = ($buildDir ? strlen($buildDir) + 1 : 0); // The +1 is to that the first / is removed so it appears relative

$shouldFail = FALSE;
foreach ($result['files'] as $file => $data) {

    echo('  "' . substr($file, $buildDirLen) . '"' . PHP_EOL);

    if (empty($data['messages'])) {

        echo('    - No errors' . PHP_EOL);

    } else {

        foreach ($data['messages'] as $msgData) {

            echo('    - ' . $msgData['type'] . ': ' . $msgData['message'] . ' at line ' . $msgData['line'] . ', symbol ' . $msgData['column'] . PHP_EOL);

            if ($msgData['type'] === 'ERROR') {
                $shouldFail = TRUE;
            }

        }

    }

    echo(PHP_EOL);

}

echo(PHP_EOL);

exit((int) $shouldFail);
