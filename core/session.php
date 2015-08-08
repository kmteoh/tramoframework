<?php
/**
 * Created by IntelliJ IDEA.
 * User: ming.teoh
 * Date: 13/05/2015
 * Time: 10:17 AM
 */

class session {
    public static final function getInstance() {
        static $instance = null;
        if (!isset($instance)) {
            $instance = new session();
            $instance->_init();
        }
        return $instance;
    }

    public function __get($name) {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    public function __set($name, $value) {
        $_SESSION[$name] = $value;
    }

    public static function __callStatic($name,$args=null) {
        $self = session::getInstance();
        call_user_func_array(array($self, $name),$args);
    }

    public static function get($name) {
        $self = session::getInstance();
        return $self->$name;
    }

    public static function set($name,$value) {
        $self = session::getInstance();
        $self->$name = $value;
        return $self;
    }

    public static function clear($name) {
        unset($_SESSION[$name]);
        return session::getInstance();
    }

    public static function kill() {
        session_destroy();
        session_regenerate_id(true);
    }

    /**
     * not required at this time
     */
    private function _init() {
    }
}