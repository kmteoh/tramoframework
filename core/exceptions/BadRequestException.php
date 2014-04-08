<?php

class BadRequestException extends HttpException {
    public $statusCode = 400;
}
