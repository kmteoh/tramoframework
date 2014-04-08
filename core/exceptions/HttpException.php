<?php

abstract class HttpException extends Exception {
    protected $statusCode;

    public function setStatusCode($statusCode) {
        return $this->statusCode = $statusCode;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }
}