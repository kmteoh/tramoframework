<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class UrlMappingErrorException extends HttpException {
    protected $statusCode = 404;
}
