<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class param {
    private $_params = array();

    private function __construct() {
    }

    public static final function getInstance() {
        static $instance = null;
        if (!isset($instance)) {
            $instance = new param();
            $instance->_init();
        }
        return $instance;
    }

    public function __get($name) {
        return isset($this->_params[$name]) ? $this->_params[$name] : null;
    }

    public function __set($name, $value) {
        $this->_params[$name] = $value;
    }

    public static function get($name) {
        $self = param::getInstance();
        return $self->$name;
    }

    private function _init() {
        if(count($_REQUEST)) {
            foreach($_REQUEST as $key => $value) {
                $this->_params[$key] = $value;
            }
        }
        $get = $_GET;
        unset($get['page']);
        unset($get['path']);
        $this->_params['query'] = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
        $this->_params['serverName'] = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname("n");
        if(php_sapi_name() != "cli") {
            $this->_params['requestMethod'] = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'cli';
            $this->_params['host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
            $this->_params['baseUrl'] = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http').'://'.$_SERVER['HTTP_HOST'].'/';
            $this->_params['isAjax'] = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            $this->_params['pagingUri'] = http_build_query($get) . (count($get)?'&':'');
            $this->_params['searchUri'] = http_build_query($get) . ($this->page!=''?((count($get)?'&':''). 'page='.$this->page):'');
            $this->_params['remoteAddr'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $this->_params['userAgent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $this->_params['cookie'] = $_COOKIE;
            $this->_params['clientIp'] = getenv('HTTP_CLIENT_IP')?:getenv('HTTP_X_FORWARDED_FOR')?:getenv('HTTP_X_FORWARDED')?:getenv('HTTP_FORWARDED_FOR')?:getenv('HTTP_FORWARDED')?:getenv('REMOTE_ADDR');
        }
    }

}