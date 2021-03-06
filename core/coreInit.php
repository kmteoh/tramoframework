<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

//runtime config
$runtimeConfig = config::getInstance();

if ($runtimeConfig->timezone != '') {
    date_default_timezone_set($runtimeConfig->timezone);
}

//init logger
config::getHandler('logger', $runtimeConfig->logger);

//import libs
if (count($runtimeConfig->import)) {
    foreach ($runtimeConfig->import as $lib) {
        import($lib);
    }
}

//init cache
$cache = null;
if ($runtimeConfig->cache) {
    $cache = config::getHandler('cache', $runtimeConfig->cache);
}

//init db
$dsn = '';
if (is_array($runtimeConfig->db)) {
    foreach ($runtimeConfig->db as $label => $dataSource) {
        if (!empty($dataSource['dbDsn'])) {
            $dsn = $dataSource['dbDsn'];
        } else if (!empty($dataSource['dbEngine'])) {
            $dsn = "{$dataSource['dbEngine']}:";
            if (!empty($dataSource['dbFile'])) {
                $dsn .= $dataSource['dbFile'];
            } else if (!empty($dataSource['dbHost']) && !empty($dataSource['dbName'])) {
                $dsn .= "host={$dataSource['dbHost']};dbname={$dataSource['dbName']}";
            }
        }
        if ($dsn) {
            $db = config::getHandler('dataSource', $label, $dsn, $dataSource['dbUser'], $dataSource['dbPassword']);
            $dataSource['profiling'] || $runtimeConfig->db['profiling'] ? $db->enableProfiling() : $db->disableProfiling();
            $cache && $cache->canCache('query') ? $db->enableCaching() : $db->disableCaching();
        }
    }
}

//show errors if asked
if (is_bool($runtimeConfig->displayError) && $runtimeConfig->displayError) {
    error_reporting(E_ALL);
    set_error_handler(array(logger::getInstance(), 'systemError'));
}