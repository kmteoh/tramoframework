<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

abstract class filter {

    abstract public function before($controller, $action);

    /**
     * @return $model
     */
    abstract public function after($controller, $action, $model);

    abstract public function afterView($controller, $action, $model, $html);

    public function beforeView($controller, $action, $model, $html){
        return $html;
    }

    public static final function getFilters() {
        $filters = array();
        foreach (getDefinedPath('filter') as $filter) {
            if (is_dir($filter)) {
                $files = scandir($filter);
                if (count($files)) {
                    foreach ($files as $file) {
                        if (is_file($filter . $file)) {
                            $className = substr($file, 0, -4);
                            $filters[] = new $className;
                        }
                    }
                }
            }
        }
        return $filters;
    }

}
