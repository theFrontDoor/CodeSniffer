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

logstr('Collecting files..');
$files = array_filter(explode(PHP_EOL, trim(shell_exec($cmd = 'git diff-tree --no-commit-id --name-only -r HEAD'))), function($value) {
    return $value !== '';
});

chdir($cwd);

logstr($cmd);

if (empty($files)) {
    logstr('No changed files found, assuming merge request, exiting', 1);
    exit(0);
}

$cmdStr = 'phpcs -d memory_limit=-1 --standard=TFD --report=json --extensions=inc,php ';
$gotFile = FALSE;

foreach ($files as $file) {

    $fullFile = $dirArg . $file;
    if (!file_exists($fullFile)) { // File got removed in git, nothing to sniff
        logstr($file . ' doesn\'t exist anymore, assuming removal and skipping', 1);
        continue;
    }

    if (substr($fullFile, -4) !== '.php' && substr($fullFile, -8) !== '.php.inc') {
        logstr($file . ' skipping due to invalid file extension', 1);
        continue;
    }

    if (is_dir($fullFile)) {
        logstr($file . ' is a directory, assuming submodule and skipping', 1);
        continue;
    }

    $gotFile = TRUE;

    logstr($file, 1);
    $cmdStr .= escapeshellarg($fullFile) . ' ';

}

if (!$gotFile) {
    logstr(PHP_EOL . 'Didn\'t find any files to lint, exiting..' . PHP_EOL);
    exit(0);
}

logstr(PHP_EOL . 'Executing: ' . $cmdStr);
$rawJSON = shell_exec($cmdStr);
$result = json_decode($rawJSON, TRUE);
if (!$result) {
    logstr('Unable to decode json:');
    logstr($rawJSON);
    exit(1);
}

logstr(PHP_EOL . 'Total: ' . PHP_EOL . '  ' . $result['totals']['errors'] . ' errors' . PHP_EOL . '  ' . $result['totals']['warnings'] . ' warnings' . PHP_EOL . '  ' . $result['totals']['fixable'] . ' fixable' . PHP_EOL);

$buildDir = getenv('CI_PROJECT_DIR');
$buildDirLen = ($buildDir ? strlen($buildDir) + 1 : 0); // The +1 is to that the first / is removed so it appears relative

$shouldFail = FALSE;
foreach ($result['files'] as $file => $data) {

    logstr('"' . substr($file, $buildDirLen) . '"', 1);

    if (empty($data['messages'])) {

        logstr('No errors', 2);

    } else {

        foreach ($data['messages'] as $msgData) {

            logstr($msgData['type'] . ': ' . $msgData['message'] . ' at line ' . $msgData['line'] . ', symbol ' . $msgData['column'], 2);

            if ($msgData['type'] === 'ERROR') {
                $shouldFail = TRUE;
            }

        }

    }

    echo(PHP_EOL);

}

echo(PHP_EOL);

exit((int) $shouldFail);

function logstr($str, $depth = 0) {

    $prefix = '';
    if ($depth > 0) {
        $prefix = str_repeat('  ', $depth) . '- ';
    }

    echo($prefix . $str . PHP_EOL);
}
