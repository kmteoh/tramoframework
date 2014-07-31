<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class config {
    private $_configs = array();

    private function __construct() {
    }

    public static final function getInstance() {
        static $instance = null;
        if (!isset($instance)) {
            $instance = new config();
            $instance->_init();
        }
        return $instance;
    }

    public function __get($name) {
        return isset($this->_configs[$name]) ? $this->_configs[$name] : null;
    }

    public function __set($name, $value) {
        $this->_configs[$name] = $value;
    }

    public static function get($name) {
        $self = config::getInstance();
        return $self->$name;
    }

    private function _init() {
        if(!file_exists(CONFIG . "dataSource.json")) {
            throw new FileNotFoundException(CONFIG . "dataSource.json is expected!");
        }
        if(!file_exists(CONFIG . "config.json")) {
            throw new FileNotFoundException(CONFIG . "config.json is expected!");
        }
        $rawConfig['dbSource'] = json_decode(file_get_contents(CONFIG . "dataSource.json"), true);
        $rawConfig['dbSource']['dbEngine'] = !empty($rawConfig['dbSource']['dbEngine']) ? $rawConfig['dbSource']['dbEngine'] : 'mysql';
        $rawConfig['config'] = array_merge(json_decode(file_get_contents(CORECONFIG . "config.json"), true), json_decode(file_get_contents(CONFIG . "config.json"), true));
        $rawConfig['config']['handlers']['dataSource'] = 'dataSource';    //hardcode for this version
        $runtimeConfig['environment'] = !empty($rawConfig['config']['environment']) ? $rawConfig['config']['environment'] : 'development';
        $runtimeConfig['template'] = !empty($rawConfig['config']['template']) ? $rawConfig['config']['template'] : 'default';

        foreach ($rawConfig as $name => $config) {
            foreach ($config as $n1 => $v1) {
                if ($n1 == "environments" && !empty($v1[$runtimeConfig['environment']])) {
                    foreach ($v1[$runtimeConfig['environment']] as $n2 => $v2) {
                        $name == 'config' ? $runtimeConfig[$n2] = $v2 : $runtimeConfig['db'][$n2] = $v2;
                    }
                } else {
                    if(empty($rawConfig[$name]['environments'][$runtimeConfig['environment']][$n1])) {
                        if (!is_array($v1)) {
                            $name == 'config' ? $runtimeConfig[$n1] = $v1 : $runtimeConfig['db'][$n1] = $v1;
                        } else {
                            foreach ($v1 as $n2 => $v2) {
                                $name == 'config' ? $runtimeConfig[$n1][$n2] = $v2 : $runtimeConfig['db'][$n1][$n2] = $v2;
                            }
                        }                            
                    }
                }
            }
        }

        //fix db datasource
        foreach($runtimeConfig['db'] as $name => $array) {
            if(strstr($name,'dataSource')) {
                foreach(array('dbEngine','dbHost','dbFile','dbDsn','dbName','dbUser','dbPassword') as $key) {
                    if(empty($array[$key]) && !empty($runtimeConfig['db'][$key])) {
                        $runtimeConfig['db'][$name][$key] = $runtimeConfig['db'][$key];
                    }
                    unset($runtimeConfig['db'][$key]);
                }
            }
        }
        $this->_configs = $runtimeConfig;
    }

    public static final function getHandler($name,$arg1=null,$arg2=null,$arg3=null,$arg4=null,$arg5=null,$arg6=null,$arg7=null,$arg8=null) {
        $config = config::getInstance();
        $handlers = $config->get('handlers');
        return !empty($handlers[$name]) && method_exists($handlers[$name],'getInstance')? $handlers[$name]::getInstance($arg1,$arg2,$arg3,$arg4,$arg5,$arg6,$arg7,$arg8) :null;
    }

    public static function isDevEnv() {
        return self::get('environment')=='development';
    }
}