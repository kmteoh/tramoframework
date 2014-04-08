<?php

class cache {
    private static $instance;

    private $_type = 'query';  //query | page; can be use as combination using plus(+) sign, e.g. query+page
    private $_storage = 'file';  //file | db; db=sqlite
    private $_path = "../.cache/";
    private $_autoCleanup = false;
    private $_filename = 'cache.sqlite'; //db only
    private $_timeout = 0;

    private function __construct($config=null) {
        if(is_array($config)) {
            foreach($config as $key => $value) {
                $this->{"_$key"} = $value;
            }
        }
        $this->_storage = ucfirst(strtolower($this->_storage));
        $this->{"_setup{$this->_storage}"}();
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

    private function _setupDb() {
        $this->_setupFile();
    }

    private function _clearFile() {
        array_map('unlink', glob("{$this->_path}/*"));
    }

    private function _clearDb() {
        //TODO
    }
}