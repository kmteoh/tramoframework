<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class cache {
    private static $instance;

    private $_type = 'query';  //query | page; can be use as combination using plus(+) sign, e.g. query+page
    private $_storage = 'file';  //file | db | apc; db=sqlite
    private $_path = "../.cache/";
    private $_autoCleanup = false;
    private $_dbFile = 'cache.sqlite'; //db only
    private $_timeout = 0;

    private function __construct($config=null) {
        if(is_array($config)) {
            foreach($config as $key => $value) {
                $this->{"_$key"} = $value;
            }
        }
        $this->_storage = ucfirst(strtolower($this->_storage));
        call_user_func(array($this,"_setup{$this->_storage}"));
    }

    public static function getInstance($config=null) {
        if (empty(cache::$instance)) {
            cache::$instance = new cache($config);
        }
        return cache::$instance;
    }

    public function canCache($type) {
        return is_string(strstr($this->_type,$type));
    }

    public function __call($name, $arguments) {
        return call_user_func_array(array($this,"_$name{$this->_storage}"), $arguments);
    }

    public static function __callStatic($name, $arguments) {
        $self = cache::getInstance();
        return call_user_func_array(array($self,"_$name{$self->_storage}"), $arguments);
    }

    private function _getFile($label,$ignoreExpiry=false) {
        $label = sha1($label);
        $cacheFile = realpath($this->_path."/$label.cache");
        if(!$cacheFile) {
            return false;
        }
        $fileTime = filemtime($cacheFile);
        if($ignoreExpiry || ($fileTime && $fileTime > (time() - $this->_timeout)) ) {
            return file_get_contents($cacheFile);
        } else {
            if($this->_autoCleanup) {
                @unlink($cacheFile);
            }
            return false;
        }
    }

    private function _setFile($label,$data) {
        $label = sha1($label);
        file_put_contents($this->_path."/".$label.".cache",$data);
    }

    private function _setupFile() {
        @mkdir($this->_path);
    }

    private function _clearFile() {
        array_map('unlink', glob("{$this->_path}/*"));
    }

    private function _setupDb() {
        $this->_setupFile();
    }

    private function _clearDb() {
        //TODO
    }

    private function _getDb($label) {
        //TODO
    }

    private function _setDb($label,$data) {
        //TODO
    }

    private function _setupApc() {
        if(!function_exists('apc_fetch')) {
            throw new BadFunctionCallException('APC not supported');
        }
    }

    private function _clearApc() {
        //TODO
    }

    private function _getApc($label) {
        return apc_fetch($label);
    }

    private function _hasApc($label) {
        return apc_exists($label);
    }

    private function _setApc($label,$data) {
        return apc_store($label, $data,$this->_timeout);
    }
}