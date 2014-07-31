<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

abstract class HttpException extends Exception {
    protected $statusCode;

    public function setStatusCode($statusCode) {
        return $this->statusCode = $statusCode;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }
}