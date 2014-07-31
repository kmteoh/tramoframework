<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

abstract class job {

    public $params = array();
    
    abstract public function validate();
    abstract public function run();

    public function __construct() {
        $services = service::getServices();
        if (count($services)) {
            foreach ($services as $service) {
                if (property_exists($this, $service))
                    $this->$service = $service::getInstance();
            }
        }
        if (property_exists($this, 'dataSource'))
            $this->dataSource = config::getHandler('dataSource');

        $this->params = config::getHandler('param');
    }
}