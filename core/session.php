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

    public static function get($name) {
        $self = session::getInstance();
        return $self->$name;
    }

    /**
     * not required at this time
     */
    private function _init() {

    }
}