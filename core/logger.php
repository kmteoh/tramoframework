<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class logger {

    const NONE = 0;
    const TRACE = 1;
    const DEBUG = 2;
    const INFO = 3;
    const WARN = 4;
    const ERROR = 5;
    const FATAL = 6;

    private static $instance;
    
    private $_levels;
    private $_appenders = array();

    private function __construct($config=null) {
        $this->_levels = array(0=>'NONE',1=>'TRACE',2=>'DEBUG',3=>'INFO',4=>'WARN',5=>'ERROR',6=>'FATAL');
        //setup appender
        if(is_array($config)) {
            foreach($config as $appender) {
                $this->_appenders[] = new appender($appender);
            }
        }
    }

    public static function getInstance($config=null) {
        if (empty(logger::$instance)) {
            logger::$instance = new logger($config);
        }
        return logger::$instance;
    }
    
    public function __call($method, $arguments) {
        $arguments[] = constant("logger::$method");
        call_user_func_array(array($this,"_handler"), $arguments);
    }

    public static function __callStatic($method, $arguments) {
        $self = logger::getInstance();
        $method = strtoupper($method);
        $arguments[] = constant("logger::$method");
        call_user_func_array(array($self,"_handler"), $arguments);
    }

    private function _handler($message = '', $level) {
        if(empty($this->_appenders)) return;
        foreach($this->_appenders as $appender) {
            if($appender->level > $level) continue;
            $appender->appendLog($this->_levels[$level],$message);
        }
    }

    public function systemError($errno, $errstr, $errfile, $errline) {
        if(error_reporting() == 0) return;  //ignore message from @ surpress
        if(empty($this->_appenders)) return;

        $systemErrors = array(
            E_ERROR => logger::ERROR,
            E_WARNING  => logger::WARN,
            E_PARSE => logger::ERROR,
            E_NOTICE => logger::INFO,
            E_USER_ERROR => logger::ERROR,
            E_USER_WARNING => logger::WARN,
            E_USER_NOTICE => logger::INFO,
            E_STRICT  => logger::WARN,
        );
        $level = !empty($systemErrors[$errno]) ? $systemErrors[$errno] : logger::DEBUG;
        foreach($this->_appenders as $appender) {
            if($appender->level > logger::FATAL) continue;
            $appender->appendLog($this->_levels[$level],$errstr,$errfile,$errline);
        }
    }
}
