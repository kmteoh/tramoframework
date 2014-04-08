<?php

class ServerErrorException extends HttpException {
    public $statusCode = 500;
}
