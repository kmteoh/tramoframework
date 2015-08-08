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

    public function toArray() {
        return $this->_params;
    }

    private function _init() {
        if(count($_REQUEST)) {
            foreach($_REQUEST as $key => $value) {
                $this->_params[$key] = $value;
            }
        }
        $get = $_GET;
        unset($get['page']);
        unset($get['_q']);
		foreach($get as $key => $value){
			$cleanGet[$key] = trim(urlencode($value));
		}
        $this->_params['endpoint'] = $this->_params['_q'];
        $this->_params['requestMethod'] = $_SERVER['REQUEST_METHOD'];
        $this->_params['isAjax'] = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $this->_params['query'] = $_SERVER['QUERY_STRING'];
        $this->_params['pagingUri'] = http_build_query($cleanGet) . (count($cleanGet)?'&':'');
        $this->_params['searchUri'] = http_build_query($cleanGet) . ($this->page!=''?((count($cleanGet)?'&':''). 'page='.$this->page):'');
        $this->_params['host'] = $_SERVER['HTTP_HOST'];
        $this->_params['baseUrl'] = (isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http').'://'.$_SERVER['HTTP_HOST'].'/';
        $this->_params['remoteAddr'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $this->_params['userAgent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $this->_params['serverName'] = $_SERVER['SERVER_NAME'];
        $this->_params['cookie'] = $_COOKIE;
        $this->_params['clientIp'] = getenv('HTTP_TRUE_CLIENT_IP')?:getenv('HTTP_CLIENT_IP')?:getenv('HTTP_X_FORWARDED_FOR')?:getenv('HTTP_X_FORWARDED')?:getenv('HTTP_FORWARDED_FOR')?:getenv('HTTP_FORWARDED')?:getenv('REMOTE_ADDR');
        $this->_params['raw'] = file_get_contents("php://input");
        $this->_params['referrer'] = $_SERVER['HTTP_REFERER'];

        if(isset($_SESSION['formToken'])) {
            $this->_params['formSecured'] = $this->_params['_token'] == $_SESSION['formToken'];
            session::clear('formToken');
        }
    }

}