<?php

/**
 * @layout main
 * @template tramoTest
 * 
 */
class testController extends controller {
    private $_test;

    public function __construct() {
        parent::__construct();
        $this->_test = new test();
    }
    
    public function unitTest() {
        return $this->_run('unitTest');
    }

    public function integrationTest() {
        return $this->_run('integrationTest');
    }

    private function _run($path) {
        $toTest = array();

        $userCase = param::get('case');
        $userUnit = param::get('unit');

        if($userCase) {
            $testCases[] = $userCase.'Test.php';
        } else {
            $testCases = scandir(FS._TEST.DS.$path);
        }
        foreach($testCases as $case) {
            $file = FS._TEST.DS.$path.DS.$case;
            if(is_file($file) && is_readable($file)) {
                include_once($file);
                $caseName = substr($case, 0, -4);
                if($userUnit) {
                    $toTest[$caseName] = array($userUnit);
                } else if($methods = get_class_methods($caseName)) {
                    $toTest[$caseName] = $methods;
                }
            }
        }

        //iterate test cases
        $totalUnits = 0; $totalSkipped = 0;
        if(count($toTest)) {
            foreach($toTest as $class => $methods) {
                $obj = new $class;
                foreach($methods as $method) {
                    $res = call_user_func(array($obj,$method));
                    if($res === false) {
                        $totalSkipped++;
                    }
                    $totalUnits++;
                }
                unset($obj);
            }
        }

        $report = $this->_test->getReport();

        return array('view'=>'test/report','title'=>'Unit Test','totalCases'=>count($toTest),'totalUnits'=>$totalUnits,'totalSkipped'=>$totalSkipped,'totalFailed'=>count($report),'totalPassed'=>($totalUnits-count($report)-$totalSkipped),'report'=>$report);

    }
}