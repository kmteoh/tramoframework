<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class service {

    protected function __construct() {

    }

    public static final function getInstance() {
        static $instances = array();

        $calledClass = get_called_class();
        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass();
            if (property_exists($instances[$calledClass], 'dataSource'))
                $instances[$calledClass]->dataSource = config::getHandler('dataSource');
            if (property_exists($instances[$calledClass], 'config'))
                $instances[$calledClass]->config = config::getInstance();

            $services = service::getServices();
            if (count($services)) {
                foreach ($services as $service) {
                    if ($calledClass!=$service && property_exists($instances[$calledClass], $service))
                        $instances[$calledClass]->$service = $service::getInstance();
                }
            }
        }

        return $instances[$calledClass];
    }

    public static final function getServices() {
        $services = array();
        foreach (getDefinedPath('service') as $path) {
            if (is_dir($path)) {
                $files = scandir($path);
                if (count($files)) {
                    foreach ($files as $file) {
                        if (is_file($path . $file)) {
                            $services[] = substr($file, 0, -4);
                        }
                    }
                }
            }
        }
        return $services;
    }

    public function __get($n) {
        return isset($GLOBALS[$n]) ? $GLOBALS[$n] : null;
    }

}
