#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */

use PhpYacc\Grammar\Context;
use PhpYacc\Generator;


if (!file_exists( __DIR__ . '/../vendor/autoload.php'))
{
    fwrite(
        STDERR,
        'You need to set up the project dependencies using Composer:' . PHP_EOL . PHP_EOL .
        '    composer install' . PHP_EOL . PHP_EOL .
        'You can learn all about Composer on https://getcomposer.org/.' . PHP_EOL
    );

    die(1);
}

require_once  __DIR__ . '/../vendor/autoload.php';

$grammarFile = __DIR__ . '/../grammar/grammar.y';
$skeleton = file_get_contents(__DIR__ . '/../grammar/parser.template.php');
$resultFile = __DIR__ . '/../src/Parser.php';

if (in_array('-h', $argv) || !file_exists($grammarFile)) {
    help();
    exit(4);
}

$errorFile = fopen('php://stderr', 'w');
$context = new Context($grammarFile, $errorFile);

for ($i = 1; $i < $argc - 1; $i++) {
    switch ($argv[$i]) {
        case '-x':
            $context->verboseDebug = true;
        case '-v':
            $context->debugFile = fopen(getcwd() . "/y.output", 'w' );
            break;
        case '-t':
            $context->tflag = true;
            break;
        case '-a':
            $context->aflag = true;
            break;
        default:
            error("Unexpected argument/flag {$argv[$i]}");

    }
}

function resolveStackAccess($code) {
    $code = preg_replace('/\$\d+/', '$this->semStack[$0]', $code);
    $code = preg_replace('/#(\d+)/', '$$1', $code);
    return $code;
}


(new Generator)->generate($context, resolveStackAccess(file_get_contents($grammarFile)), $skeleton, $resultFile);

function help()
{
    echo <<<EOH
Type resolver builder 
Powered by: PHP-Yacc by Anthony Ferrara, Nikita Popov, and others
Usage: phpyacc [options] grammar.y
Options:
  -p <name>     The name of the class to generate
  -x            Enable extended debug mode
  -v            Generate y.output file
  -t            Set the T flag for templates (inclusion of debug information)
  -a            Set the A flag for templates (unused)
  -m <skeleton> Path to the skeleton file to use
EOH;
}

function error(string $message)
{
    echo $message . "\n";
    help();
    exit(2);
}
