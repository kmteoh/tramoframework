<?php

include_once('applicationTop.php');

define('_JOB','jobs');

import('job');

$start = microSeconds();
error_reporting(0);
ini_set('display_errors',false);

spl_autoload_register('autoloader');
register_shutdown_function('shutdown');
set_exception_handler('uncaughtException');

$usage = "Usage: php core\cron.php -f <jobName> ";

$class = getopt('f:');
if(empty($class)) {
    die("Job name not given! \n$usage\n\n");
}
$className = $class['f'] . 'Job';
include_once(FS._JOB.DS.$className.'.php');
if(!class_exists($className)) {
    die("Job class $className not exists! \n$usage\n\n");
}

//init
try {
    require_once(__DIR__.DS.'coreInit.php');
} catch (Exception $e) {
    echo get_class($e) .': ' . $e->getMessage() . "\n";
    die($e->getTraceAsString()."\n\n");
}

//finally
$jobObj = new $className;
if(!$jobObj->validate()) {
    $rc = new ReflectionClass($className);
    $jobDoc = $rc->getDocComment();
    preg_match("/@usage (.*)/", $jobDoc, $m1);
    if(!empty($m1)) {
        $usage .= $m1[1];
    }
    echo "Invalid arguments detected while running $className job! ".PHP_EOL;
    die($usage." \n\n");
}

$jobObj->run();
