<?php
namespace Permengandum\Kmeans\Exceptions;

class ConflictException extends HttpException
{
    public function __construct($message = 'Conflict', \Exception $previous = null, array $headers = array())
    {
        parent::__construct(409, $message, $previous, $headers);
    }
}
