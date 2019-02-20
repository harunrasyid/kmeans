<?php
namespace Permengandum\Kmeans\Exceptions;

class BadRequestException extends HttpException
{
    public function __construct($message = 'Bad Request', \Exception $previous = null, array $headers = array())
    {
        parent::__construct(400, $message, $previous, $headers);
    }
}
