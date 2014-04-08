<?php

define('_TEST', 'tests');

class test {

    private $_reports;

    public function __construct() {
        assert_options(ASSERT_ACTIVE, true);
        assert_options(ASSERT_BAIL, false);
        assert_options(ASSERT_WARNING, false);
        assert_options(ASSERT_QUIET_EVAL, true);
        assert_options(ASSERT_CALLBACK, array($this, 'failure'));

        $this->_reports = array();
        $dataSource = config::getHandler('dataSource');
        $dataSource->beginTransaction();
    }

    public function __destruct() {
        $dataSource = config::getHandler('dataSource');
        $dataSource->rollback();
    }

    public function failure($file, $line, $code, $desc = '') {
        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->_reports[] = array(
            'Case' => $debug[2]['class'],
            'Unit' => $debug[2]['function'],
            'File' => $file.' #'.$line
        );
    }

    public function getReport() {
        return $this->_reports;
    }

}
